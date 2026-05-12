-- =========================================================================
--  LabSync Seed Data — Test fixtures for development
-- =========================================================================
--  WARNING: This file deletes all existing data and inserts fresh fixtures.
--  Use only in development environments.
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing data
TRUNCATE TABLE SystemAuditLogs;
TRUNCATE TABLE SessionConsumables;
TRUNCATE TABLE Sessions;
TRUNCATE TABLE GrantPartitions;
TRUNCATE TABLE GrantTransactions;
TRUNCATE TABLE GrantUserAccess;
TRUNCATE TABLE Grants;
TRUNCATE TABLE IncidentReports;
TRUNCATE TABLE HazmatWarnings;
TRUNCATE TABLE SafetyBriefingAcknowledgements;
TRUNCATE TABLE SafetyBriefings;
TRUNCATE TABLE ScheduleBuffers;
TRUNCATE TABLE Bookings;
TRUNCATE TABLE Consumables;
TRUNCATE TABLE EquipmentDependencies;
TRUNCATE TABLE Equipment;
TRUNCATE TABLE GuestResearchers;
TRUNCATE TABLE Users;
TRUNCATE TABLE RateTiers;
TRUNCATE TABLE ComplianceConfig;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
--  USERS
--  All passwords below are: "password123"
--  Hash generated with: password_hash('password123', PASSWORD_DEFAULT)
-- =========================================================================
INSERT INTO Users
    (userID, userName, userPassword, userType, userStatus, isExternal,
     clearanceLevel, maxBookingHoursPerWeek, safetyBriefingAcknowledged)
VALUES
    -- Lab Manager (you'll log in as this one to test IncidentController)
    (1, 'manager_ahmed',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'lab_manager', 'active', FALSE, 5, 40, TRUE),

    -- Faculty PI
    (2, 'pi_smith',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'faculty_pi', 'active', FALSE, 5, 40, TRUE),

    -- Internal researcher (active)
    (3, 'researcher_mahmoud',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'researcher', 'active', FALSE, 2, 20, TRUE),

    -- Internal researcher (low clearance, for testing denial messages)
    (4, 'researcher_sara',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'researcher', 'active', FALSE, 1, 20, TRUE),

    -- External researcher
    (5, 'researcher_external',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'researcher', 'active', TRUE, 2, 15, TRUE),

    -- Guest researcher (active, valid expiration)
    (6, 'guest_alice',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'guest_researcher', 'active', TRUE, 1, 10, FALSE),

    -- Guest researcher (about to expire — for testing expiry workflow)
    (7, 'guest_bob_expiring',
     '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'guest_researcher', 'active', TRUE, 1, 10, FALSE);

-- =========================================================================
--  GUEST RESEARCHERS
-- =========================================================================
INSERT INTO GuestResearchers (userID, institution, expirationDate, sponsorPIID)
VALUES
    (6, 'MIT',                DATE_ADD(CURDATE(), INTERVAL 90 DAY), 2),
    (7, 'Stanford University', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 2);

-- =========================================================================
--  EQUIPMENT
-- =========================================================================
INSERT INTO Equipment
    (equipmentID, equipmentName, equipmentStatus, requiredClearanceLevel,
     isDualUse, hourlyRateExternal, overheadPercentage,
     powerUpBufferMinutes, coolDownBufferMinutes, calibrationThresholdHours)
VALUES
    (1, 'Confocal Microscope',     'available', 1, FALSE, 50.00, 15.00, 10, 5,  100),
    (2, 'Mass Spectrometer',       'available', 2, FALSE, 120.00, 20.00, 15, 10, 200),
    (3, 'Electron Microscope',     'available', 3, TRUE,  300.00, 25.00, 30, 20, 150),
    (4, 'Centrifuge',              'available', 0, FALSE, 25.00, 10.00, 5,  5,  500),
    (5, 'Laser Cutter',            'available', 2, FALSE, 80.00, 15.00, 10, 10, 200),
    (6, 'Quantum Computer Sim',    'available', 4, TRUE,  500.00, 30.00, 20, 15, 100);

-- =========================================================================
--  EQUIPMENT DEPENDENCIES (Sequential Booking Dependency)
--  Primary equipment is auto-booked together with its secondary equipment.
-- =========================================================================
INSERT INTO EquipmentDependencies (primaryEquipmentID, secondaryEquipmentID)
VALUES
    (2, 4),  -- Mass Spectrometer (primary) requires Centrifuge (secondary)
    (3, 4),  -- Electron Microscope (primary) requires Centrifuge (secondary)
    (1, 4);  -- Confocal Microscope (primary) requires Centrifuge (secondary)

-- =========================================================================
--  SAFETY BRIEFINGS
-- =========================================================================
INSERT INTO SafetyBriefings (briefingID, equipmentID, briefingContent)
VALUES
    (1, 1, 'Always wear protective eyewear. Do not touch the laser directly.'),
    (2, 2, 'Handle samples with care. Dispose of waste in designated containers.'),
    (3, 3, 'CRITICAL: Restricted access. Read full SOP before each use.'),
    (4, 5, 'Keep flammable materials away. Wear flame-resistant gloves.');

-- =========================================================================
--  HAZMAT WARNINGS
-- =========================================================================
INSERT INTO HazmatWarnings (equipmentID, hazardType, warningMessage, disposalInstructions)
VALUES
    (3, 'Radiation', 'X-ray emission risk', 'Dispose used filters in lead container'),
    (5, 'Fire',      'High temperature operation', 'Allow 30 minutes cooldown before maintenance');

-- =========================================================================
--  GRANTS
-- =========================================================================
INSERT INTO Grants
    (grantID, grantName, piID, totalBudget, currentBalance,
     grantStatus, expirationDate)
VALUES
    (1, 'NSF Research Grant 2026',      2, 50000.00, 45000.00, 'active',
        DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
    (2, 'NIH Bio Research',             2, 30000.00, 28500.00, 'active',
        DATE_ADD(CURDATE(), INTERVAL 6 MONTH)),
    (3, 'Expired Test Grant',           2, 10000.00, 5000.00, 'expired',
        DATE_SUB(CURDATE(), INTERVAL 30 DAY));

-- =========================================================================
--  GRANT USER ACCESS (who can use which grants)
-- =========================================================================
INSERT INTO GrantUserAccess (grantID, userID, billingPercentage)
VALUES
    (1, 3, 100.00),  -- mahmoud → NSF grant
    (1, 4, 50.00),   -- sara → NSF grant (50%)
    (2, 4, 50.00),   -- sara → NIH grant (50%)
    (2, 6, 100.00);  -- guest alice → NIH grant

-- =========================================================================
--  RATE TIERS
-- =========================================================================
INSERT INTO RateTiers (userType, isExternal, rateMultiplier, description)
VALUES
    ('researcher',       FALSE, 1.00, 'Internal researcher base rate'),
    ('researcher',       TRUE,  1.50, 'External researcher (50% surcharge)'),
    ('guest_researcher', TRUE,  1.75, 'Guest researcher (75% surcharge)'),
    ('faculty_pi',       FALSE, 0.80, 'Faculty discount (20% off)');

-- =========================================================================
--  COMPLIANCE CONFIG
-- =========================================================================
INSERT INTO ComplianceConfig (configKey, configValue, description)
VALUES
    ('dual_use_min_clearance', '3', 'Minimum clearance level for dual-use equipment'),
    ('guest_max_days',          '365', 'Maximum onboarding period for guests'),
    ('safety_briefing_validity_days', '180', 'How long safety briefing acknowledgement lasts');

-- =========================================================================
--  CONSUMABLES
-- =========================================================================
INSERT INTO Consumables (consumableName, unitCost, stockQuantity, equipmentID)
VALUES
    ('Microscope Slides',      2.50,  500, 1),
    ('MS Sample Tubes',        1.20, 1000, 2),
    ('EM Grids',              15.00,  200, 3),
    ('Centrifuge Tubes',       0.50, 2000, 4),
    ('Laser Cutting Material', 5.00,  300, 5);

-- =========================================================================
--  SAMPLE BOOKINGS (for testing cancellations during incident lockout)
-- =========================================================================
INSERT INTO Bookings
    (userID, equipmentID, startTime, endTime, bookingStatus, grantID)
VALUES
    (3, 1, DATE_ADD(NOW(), INTERVAL 1 DAY),  DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 2 HOUR, 'confirmed', 1),
    (3, 2, DATE_ADD(NOW(), INTERVAL 3 DAY),  DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 1 HOUR, 'confirmed', 1),
    (4, 1, DATE_ADD(NOW(), INTERVAL 2 DAY),  DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR, 'pending',   1),
    (6, 4, DATE_ADD(NOW(), INTERVAL 1 DAY),  DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 1 HOUR, 'confirmed', 2);

-- =========================================================================
--  Done!
-- =========================================================================
SELECT 'Seed data loaded successfully!' AS message;
SELECT COUNT(*) AS total_users     FROM Users;
SELECT COUNT(*) AS total_equipment FROM Equipment;
SELECT COUNT(*) AS total_grants    FROM Grants;
SELECT COUNT(*) AS total_bookings  FROM Bookings;