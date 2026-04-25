SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE Users (

    userID INT PRIMARY KEY AUTO_INCREMENT,

    userName VARCHAR(100) NOT NULL,

    userPassword VARCHAR(255) NOT NULL, 

    userType ENUM('researcher', 'guest_researcher', 'faculty_pi', 'lab_manager', 'admin') NOT NULL,

    userStatus ENUM('active', 'suspended', 'inactive') DEFAULT 'active',

    isExternal BOOLEAN DEFAULT FALSE,

    clearanceLevel INT DEFAULT 0,

    maxBookingHoursPerWeek INT DEFAULT 20,

    currentWeeklyBookedHours DECIMAL(5,2) DEFAULT 0.00,

    safetyBriefingAcknowledged BOOLEAN DEFAULT FALSE,

    safetyBriefingAcknowledgedAt DATETIME NULL,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;



CREATE TABLE GuestResearchers (

    userID INT PRIMARY KEY,

    institution VARCHAR(150) NOT NULL,

    expirationDate DATE NOT NULL,

    sponsorPIID INT NOT NULL,

        CONSTRAINT fk_guest_user 

        FOREIGN KEY (userID) REFERENCES Users(userID) 

        ON DELETE CASCADE,

        CONSTRAINT fk_guest_sponsor 

        FOREIGN KEY (sponsorPIID) REFERENCES Users(userID)

) ENGINE=InnoDB;



CREATE TABLE Equipment (

    equipmentID INT PRIMARY KEY AUTO_INCREMENT,

    equipmentName VARCHAR(200) NOT NULL,

    equipmentStatus ENUM('available', 'in_use', 'locked_out', 'under_maintenance', 'calibration_needed') DEFAULT 'available',

    requiredClearanceLevel INT DEFAULT 0,

    isDualUse BOOLEAN DEFAULT FALSE,

    totalUsageHours DECIMAL(10,2) DEFAULT 0.00,

    calibrationThresholdHours DECIMAL(10,2) DEFAULT 100.00,

    currentCalibrationHours DECIMAL(10,2) DEFAULT 0.00,

    powerUpBufferMinutes INT DEFAULT 0,

    coolDownBufferMinutes INT DEFAULT 0,

    hourlyRateExternal DECIMAL(10,2) NOT NULL,

    overheadPercentage DECIMAL(5,2) DEFAULT 0.00,

    lockoutReason TEXT NULL,

    lockedOutAt DATETIME NULL,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;



CREATE TABLE EquipmentDependencies (

    dependencyID INT PRIMARY KEY AUTO_INCREMENT,

    primaryEquipmentID INT NOT NULL,

    secondaryEquipmentID INT NOT NULL,

    CONSTRAINT fk_dependency_primary 

        FOREIGN KEY (primaryEquipmentID) REFERENCES Equipment(equipmentID) 

        ON DELETE CASCADE,   

    CONSTRAINT fk_dependency_secondary 

        FOREIGN KEY (secondaryEquipmentID) REFERENCES Equipment(equipmentID) 

        ON DELETE CASCADE,

    UNIQUE KEY uniqueDependency (primaryEquipmentID, secondaryEquipmentID)

) ENGINE=InnoDB;



CREATE TABLE Bookings (

    bookingID INT PRIMARY KEY AUTO_INCREMENT,

    userID INT NOT NULL,

    equipmentID INT NOT NULL,

    startTime DATETIME NOT NULL,

    endTime DATETIME NOT NULL,

    bookingStatus ENUM('confirmed', 'pending', 'cancelled', 'completed', 'rejected', 'no_show') DEFAULT 'pending',

    isAutoBooked BOOLEAN DEFAULT FALSE, 

    parentBookingID INT NULL, 

    grantID INT NULL, 

    labManagerID INT NULL, 

    cancellationReason TEXT NULL,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_user 

        FOREIGN KEY (userID) REFERENCES Users(userID),

    CONSTRAINT fk_booking_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID),

    CONSTRAINT fk_booking_parent 

        FOREIGN KEY (parentBookingID) REFERENCES Bookings(bookingID),

    CONSTRAINT fk_booking_manager 

        FOREIGN KEY (labManagerID) REFERENCES Users(userID)

) ENGINE=InnoDB;



CREATE TABLE ScheduleBuffers (

    bufferID INT PRIMARY KEY AUTO_INCREMENT,

    bookingID INT NOT NULL,

    equipmentID INT NOT NULL,

    bufferType ENUM('power_up', 'cool_down') NOT NULL,

    startTime DATETIME NOT NULL,

    endTime DATETIME NOT NULL,

    CONSTRAINT fk_buffer_booking 

        FOREIGN KEY (bookingID) REFERENCES Bookings(bookingID) 

        ON DELETE CASCADE,

    CONSTRAINT fk_buffer_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID) 

        ON DELETE CASCADE

) ENGINE=InnoDB;



CREATE TABLE Sessions (

    sessionID INT PRIMARY KEY AUTO_INCREMENT,

    bookingID INT NOT NULL,

    userID INT NOT NULL,

    equipmentID INT NOT NULL,

    actualStartTime DATETIME NOT NULL,

    actualEndTime DATETIME NULL,

    durationHours DECIMAL(5,2) NULL,

    totalCost DECIMAL(10,2) NULL,

    sessionStatus ENUM('active', 'completed', 'terminated') DEFAULT 'active',

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_session_booking 

        FOREIGN KEY (bookingID) REFERENCES Bookings(bookingID),

    CONSTRAINT fk_session_user 

        FOREIGN KEY (userID) REFERENCES Users(userID),

    CONSTRAINT fk_session_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID)

) ENGINE=InnoDB;



CREATE TABLE Grants (

    grantID INT PRIMARY KEY AUTO_INCREMENT,

    grantName VARCHAR(200) NOT NULL,

    piID INT NOT NULL,

    totalBudget DECIMAL(12,2) NOT NULL,

    currentBalance DECIMAL(12,2) NOT NULL,

    grantStatus ENUM('active', 'expired', 'depleted', 'inactive') DEFAULT 'active',

    expirationDate DATE NOT NULL,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_grant_pi 

        FOREIGN KEY (piID) REFERENCES Users(userID)

) ENGINE=InnoDB;



CREATE TABLE GrantUserAccess (

    accessID INT PRIMARY KEY AUTO_INCREMENT,

    grantID INT NOT NULL,

    userID INT NOT NULL,

    billingPercentage DECIMAL(5,2) DEFAULT 100.00, 

    CONSTRAINT fk_access_grant 

        FOREIGN KEY (grantID) REFERENCES Grants(grantID) 

        ON DELETE CASCADE,

    CONSTRAINT fk_access_user 

        FOREIGN KEY (userID) REFERENCES Users(userID) 

        ON DELETE CASCADE,

    UNIQUE KEY uniqueGrantUser (grantID, userID)

) ENGINE=InnoDB;



CREATE TABLE GrantTransactions (

    transactionID INT PRIMARY KEY AUTO_INCREMENT,

    grantID INT NOT NULL,

    sessionID INT NULL,

    bookingID INT NULL,

    userID INT NOT NULL,

    amount DECIMAL(10,2) NOT NULL,

    transactionType ENUM('deduction', 'refund', 'reallocation_in', 'reallocation_out') NOT NULL,

    description TEXT,

    baseCost DECIMAL(10,2) NULL,

    consumableCost DECIMAL(10,2) DEFAULT 0.00,

    overheadCost DECIMAL(10,2) DEFAULT 0.00,

    approvalStatus ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',

    approvedByPIID INT NULL,

    approvedAt DATETIME NULL,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_trans_grant 

        FOREIGN KEY (grantID) REFERENCES Grants(grantID),

    CONSTRAINT fk_trans_session 

        FOREIGN KEY (sessionID) REFERENCES Sessions(sessionID),

    CONSTRAINT fk_trans_booking 

        FOREIGN KEY (bookingID) REFERENCES Bookings(bookingID),

    CONSTRAINT fk_trans_user 

        FOREIGN KEY (userID) REFERENCES Users(userID),

    CONSTRAINT fk_trans_pi 

        FOREIGN KEY (approvedByPIID) REFERENCES Users(userID)

) ENGINE=InnoDB;



CREATE TABLE GrantPartitions (

    partitionID INT PRIMARY KEY AUTO_INCREMENT,

    transactionID INT NOT NULL,

    grantID INT NOT NULL,

    percentage DECIMAL(5,2) NOT NULL, 

    amountDeducted DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_partition_transaction 

        FOREIGN KEY (transactionID) REFERENCES GrantTransactions(transactionID) 

        ON DELETE CASCADE,

    CONSTRAINT fk_partition_grant 

        FOREIGN KEY (grantID) REFERENCES Grants(grantID)

) ENGINE=InnoDB;



CREATE TABLE SafetyBriefings (

    briefingID INT PRIMARY KEY AUTO_INCREMENT,

    equipmentID INT NOT NULL,

    briefingContent TEXT NOT NULL,

    CONSTRAINT fk_briefing_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID) 

        ON DELETE CASCADE

) ENGINE=InnoDB;



CREATE TABLE SafetyBriefingAcknowledgements (

    ackID INT PRIMARY KEY AUTO_INCREMENT,

    userID INT NOT NULL,

    briefingID INT NOT NULL,

    acknowledgedAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ack_user 

        FOREIGN KEY (userID) REFERENCES Users(userID) 

        ON DELETE CASCADE,

    CONSTRAINT fk_ack_briefing 

        FOREIGN KEY (briefingID) REFERENCES SafetyBriefings(briefingID) 

        ON DELETE CASCADE,

    UNIQUE KEY unique_user_briefing (userID, briefingID)

) ENGINE=InnoDB;



CREATE TABLE IncidentReports (

    incidentID INT PRIMARY KEY AUTO_INCREMENT,

    userID INT NOT NULL,

    equipmentID INT NOT NULL,

    reportedByID INT NOT NULL,

    incidentType VARCHAR(100) NOT NULL,

    description TEXT NOT NULL,

    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,

    timeOfIncident DATETIME NOT NULL,

    CONSTRAINT fk_incident_user 

        FOREIGN KEY (userID) REFERENCES Users(userID),

    CONSTRAINT fk_incident_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID),

    CONSTRAINT fk_incident_reporter 

        FOREIGN KEY (reportedByID) REFERENCES Users(userID)

) ENGINE=InnoDB;



CREATE TABLE Consumables (

    consumableID INT PRIMARY KEY AUTO_INCREMENT,

    consumableName VARCHAR(200) NOT NULL,

    unitCost DECIMAL(10,2) NOT NULL,

    stockQuantity INT DEFAULT 0,

    equipmentID INT NULL,

    CONSTRAINT fk_consumable_equipment 

        FOREIGN KEY (equipmentID) REFERENCES Equipment(equipmentID) 

        ON DELETE SET NULL

) ENGINE=InnoDB;



CREATE TABLE SessionConsumables (

    sessionConsumableID INT PRIMARY KEY AUTO_INCREMENT,

    sessionID INT NOT NULL,

    consumableID INT NOT NULL,

    quantityUsed INT NOT NULL,

    totalCost DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_sesscon_session 

        FOREIGN KEY (sessionID) REFERENCES Sessions(sessionID) 

        ON DELETE CASCADE,

    CONSTRAINT fk_sesscon_consumable 

        FOREIGN KEY (consumableID) REFERENCES Consumables(consumableID)

) ENGINE=InnoDB;



CREATE TABLE SystemAuditLogs (

    logID INT PRIMARY KEY AUTO_INCREMENT,

    userID INT NULL,

    actionType VARCHAR(100) NOT NULL,

    tableAffected VARCHAR(100) NOT NULL,

    recordID INT NULL,

    oldValue TEXT NULL,

    newValue TEXT NULL,

    ipAddress VARCHAR(45) NULL,

    description TEXT,

    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB;



CREATE TABLE RateTiers (

    tierID INT PRIMARY KEY AUTO_INCREMENT,

    userType ENUM('researcher', 'guest_researcher', 'faculty_pi') NOT NULL,

    isExternal BOOLEAN DEFAULT FALSE,

    rateMultiplier DECIMAL(5,2) DEFAULT 1.00,

    description VARCHAR(200),

    UNIQUE KEY unique_tier (userType, isExternal)

) ENGINE=InnoDB;



CREATE TABLE ComplianceConfig (

    configID INT PRIMARY KEY AUTO_INCREMENT,

    configKey VARCHAR(100) UNIQUE NOT NULL,

    configValue TEXT NOT NULL,

    description TEXT,

    updatedBy INT NULL,

    lastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_config_admin 

        FOREIGN KEY (updatedBy) REFERENCES Users(userID) 

        ON DELETE SET NULL

) ENGINE=InnoDB;