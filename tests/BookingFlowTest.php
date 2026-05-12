<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../modules/equipment/SequentialBooking.php';


class BookingFlowTest extends TestCase
{
    private $service;
    private $mockBooking;
    private $mockEquipment;
    private $mockPdo;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../models/Booking.php';
        require_once __DIR__ . '/../models/Equipment.php';

        $this->mockPdo = $this->createMock(PDO::class);
        injectMockPdoIntoDatabase($this->mockPdo);
        $this->mockBooking   = $this->createMock(Booking::class);
        $this->mockEquipment = $this->createMock(Equipment::class);
        $this->service = new SequentialBookingService();

        $ref = new ReflectionClass(SequentialBookingService::class);

        $bookingProp = $ref->getProperty('bookingModel');
        $bookingProp->setAccessible(true);
        $bookingProp->setValue($this->service, $this->mockBooking);

        $equipmentProp = $ref->getProperty('equipmentModel');
        $equipmentProp->setAccessible(true);
        $equipmentProp->setValue($this->service, $this->mockEquipment);

        $dbProp = $ref->getProperty('db');
        $dbProp->setAccessible(true);
        $dbProp->setValue($this->service, $this->mockPdo);
    }

    public function test_booking_succeeds_when_no_conflict_and_no_dependencies()
    {
        $this->mockBooking->method('hasTimeConflict')->willReturn(false);
        $this->mockEquipment->method('getDependencies')->willReturn([]);
        $this->mockBooking->method('createBooking')
                          ->willReturn(['bookingID' => 101, 'briefingContent' => '']);

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 4,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(101, $result['primaryBookingID']);
    }

    public function test_booking_fails_when_primary_equipment_has_conflict()
    {
        $this->mockBooking->method('hasTimeConflict')->willReturn(true);

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 2,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Primary equipment', $result['message']);
    }

    public function test_booking_fails_when_secondary_equipment_has_conflict()
    {
        $this->mockBooking->method('hasTimeConflict')
                          ->willReturnOnConsecutiveCalls(false, true);

        $this->mockEquipment->method('getDependencies')->willReturn([
            ['equipmentID' => 4, 'equipmentName' => 'Centrifuge'],
        ]);

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 2,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Centrifuge', $result['message']);
        $this->assertStringContainsString('incomplete setup', $result['message']);
    }

    public function test_booking_with_dependencies_creates_primary_plus_one_secondary()
    {
        $this->mockBooking->method('hasTimeConflict')->willReturn(false);
        $this->mockEquipment->method('getDependencies')->willReturn([
            ['equipmentID' => 4, 'equipmentName' => 'Centrifuge'],
        ]);

        $this->mockBooking->expects($this->exactly(2))
                          ->method('createBooking')
                          ->willReturnOnConsecutiveCalls(
                              ['bookingID' => 201, 'briefingContent' => ''], 
                              ['bookingID' => 202, 'briefingContent' => '']  
                          );

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 2,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(201, $result['primaryBookingID']);
    }

    public function test_booking_rolls_back_when_primary_creation_fails()
    {
        $this->mockBooking->method('hasTimeConflict')->willReturn(false);
        $this->mockEquipment->method('getDependencies')->willReturn([]);
        $this->mockBooking->method('createBooking')->willReturn(false);
        $this->mockPdo->expects($this->once())->method('rollBack');
        $this->mockPdo->expects($this->never())->method('commit');

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 4,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertFalse($result['success']);
    }

    public function test_successful_booking_commits_the_transaction()
    {
        $this->mockBooking->method('hasTimeConflict')->willReturn(false);
        $this->mockEquipment->method('getDependencies')->willReturn([]);
        $this->mockBooking->method('createBooking')
                          ->willReturn(['bookingID' => 999, 'briefingContent' => '']);

        $this->mockPdo->expects($this->once())->method('commit');
        $this->mockPdo->expects($this->never())->method('rollBack');

        $result = $this->service->bookWithDependencies(
            userID: 3,
            primaryEquipmentID: 4,
            startTime: '2026-05-31 10:00:00',
            endTime:   '2026-05-31 12:00:00'
        );

        $this->assertTrue($result['success']);
    }
}
