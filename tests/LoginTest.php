<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Researcher.php';


class LoginTest extends TestCase
{
    private $userModel;
    private $mockPdo;
    private $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo  = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);
        injectMockPdoIntoDatabase($this->mockPdo);
        $this->userModel = new Researcher();
    }

    public function test_valid_credentials_return_user_array()
    {
        $hashed = password_hash('password123', PASSWORD_DEFAULT);
        $this->mockStmt->method('fetch')->willReturn([
            'userID'       => 3,
            'userName'     => 'researcher_mahmoud',
            'userPassword' => $hashed,
            'userType'     => 'researcher',
            'userStatus'   => 'active',
        ]);

        $result = $this->userModel->authenticate('researcher_mahmoud', 'password123');

        $this->assertIsArray($result);
        $this->assertEquals('researcher_mahmoud', $result['userName']);
    }

    public function test_password_is_stripped_from_returned_user()
    {
        $hashed = password_hash('password123', PASSWORD_DEFAULT);
        $this->mockStmt->method('fetch')->willReturn([
            'userID'       => 3,
            'userName'     => 'researcher_mahmoud',
            'userPassword' => $hashed,
            'userStatus'   => 'active',
        ]);

        $result = $this->userModel->authenticate('researcher_mahmoud', 'password123');

        $this->assertArrayNotHasKey('userPassword', $result);
    }

    public function test_wrong_password_returns_false()
    {
        $hashed = password_hash('password123', PASSWORD_DEFAULT);
        $this->mockStmt->method('fetch')->willReturn([
            'userID'       => 3,
            'userName'     => 'researcher_mahmoud',
            'userPassword' => $hashed,
            'userStatus'   => 'active',
        ]);

        $result = $this->userModel->authenticate('researcher_mahmoud', 'wrong_password');

        $this->assertFalse($result);
    }

    public function test_nonexistent_user_returns_false()
    {
        $this->mockStmt->method('fetch')->willReturn(false);

        $result = $this->userModel->authenticate('ghost_user', 'anything');

        $this->assertFalse($result);
    }

    public function test_suspended_user_returns_false()
    {
        $this->mockStmt->method('fetch')->willReturn(false);

        $result = $this->userModel->authenticate('suspended_user', 'password123');

        $this->assertFalse($result);
    }

    public function test_empty_password_returns_false()
    {
        $hashed = password_hash('password123', PASSWORD_DEFAULT);
        $this->mockStmt->method('fetch')->willReturn([
            'userID'       => 3,
            'userName'     => 'researcher_mahmoud',
            'userPassword' => $hashed,
            'userStatus'   => 'active',
        ]);

        $result = $this->userModel->authenticate('researcher_mahmoud', '');

        $this->assertFalse($result);
    }
}
