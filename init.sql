-- Vytvoření databáze
CREATE DATABASE IF NOT EXISTS jidelnicek;
USE jidelnicek;

-- Zakázání kontrol integrity během mazání tabulek
SET FOREIGN_KEY_CHECKS=0;

-- Drop Tables
DROP TABLE IF EXISTS `Celajidla`;
DROP TABLE IF EXISTS `Hlavnicastijidel`;
DROP TABLE IF EXISTS `Kategorie`;
DROP TABLE IF EXISTS `Oblibenejidla`;
DROP TABLE IF EXISTS `Omacky`;
DROP TABLE IF EXISTS `Prilohy`;
DROP TABLE IF EXISTS `Typyjidla`;
DROP TABLE IF EXISTS `Uzivatele`;

-- Create Tables
CREATE TABLE `Hlavnicastijidel`
(
	`HlavnicastjidlaID` INT NOT NULL AUTO_INCREMENT,
	`Nazev` VARCHAR(50) NOT NULL,
	`Pocetsnezeni` TINYINT NOT NULL,
	PRIMARY KEY (`HlavnicastjidlaID`)
);

CREATE TABLE `Kategorie`
(
	`KategorieID` INT NOT NULL AUTO_INCREMENT,
	`PredkrmHlavniDezert` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`KategorieID`)
);

CREATE TABLE `Omacky`
(
	`OmackaID` INT NOT NULL AUTO_INCREMENT,
	`Nazev` VARCHAR(50) NOT NULL,
	`Pocetsnezeni` TINYINT NOT NULL,
	PRIMARY KEY (`OmackaID`)
);

CREATE TABLE `Prilohy`
(
	`PrilohaID` INT NOT NULL AUTO_INCREMENT,
	`Nazev` VARCHAR(50) NOT NULL,
	`Pocetsnezeni` TINYINT NOT NULL,
	PRIMARY KEY (`PrilohaID`)
);

CREATE TABLE `Typyjidla`
(
	`TypjidlaID` INT NOT NULL AUTO_INCREMENT,
	`SnidaneObedVecereSvacina` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`TypjidlaID`)
);

CREATE TABLE `Uzivatele`
(
	`UzivatelID` INT NOT NULL AUTO_INCREMENT,
	`Admin` BOOL NOT NULL,
	`Jmeno` VARCHAR(50) NOT NULL,
	`Prijmeni` VARCHAR(50) NOT NULL,
	`Heslo` VARCHAR(255) NOT NULL,
	`Obrazek` LONGBLOB,
	PRIMARY KEY (`UzivatelID`)
);

CREATE TABLE `Celajidla`
(
	`CelejidloID` INT NOT NULL AUTO_INCREMENT,
	`HlavnicastjidlaID` INT NOT NULL,
	`UzivatelID` INT NOT NULL,
	`OmackaID` INT NOT NULL,
	`PrilohaID` INT NOT NULL,
	`KategorieID` INT NOT NULL,
	`Pocetsnezenikombinace` TINYINT NOT NULL,
	`TypjidlaID` INT NOT NULL,
	`Obloha` BOOL NOT NULL,
	`Casjidla` DATETIME,
	PRIMARY KEY (`CelejidloID`),
	FOREIGN KEY (`HlavnicastjidlaID`) REFERENCES `Hlavnicastijidel` (`HlavnicastjidlaID`),
	FOREIGN KEY (`UzivatelID`) REFERENCES `Uzivatele` (`UzivatelID`),
	FOREIGN KEY (`OmackaID`) REFERENCES `Omacky` (`OmackaID`),
	FOREIGN KEY (`PrilohaID`) REFERENCES `Prilohy` (`PrilohaID`),
	FOREIGN KEY (`KategorieID`) REFERENCES `Kategorie` (`KategorieID`),
	FOREIGN KEY (`TypjidlaID`) REFERENCES `Typyjidla` (`TypjidlaID`)
);

CREATE TABLE `Oblibenejidla`
(
	`OblibenejidloID` INT NOT NULL AUTO_INCREMENT,
	`CelejidloID` INT NOT NULL,
	`UzivatelID` INT NOT NULL,
	PRIMARY KEY (`OblibenejidloID`),
	FOREIGN KEY (`CelejidloID`) REFERENCES `Celajidla` (`CelejidloID`),
	FOREIGN KEY (`UzivatelID`) REFERENCES `Uzivatele` (`UzivatelID`)
);

-- Obnovení kontrol integrity
SET FOREIGN_KEY_CHECKS=1;

-- INSERTS
INSERT INTO Hlavnicastijidel (Nazev, Pocetsnezeni) VALUES
('kureci', 4),
('losos', 3),
('hovezi', 5);

INSERT INTO Kategorie (PredkrmHlavniDezert) VALUES
('Starter'),
('Main Course'),
('Dessert');

INSERT INTO Omacky (Nazev, Pocetsnezeni) VALUES
('tatarka', 2),
('kecup', 3),
('horcice', 1);

INSERT INTO Prilohy (Nazev, Pocetsnezeni) VALUES
('ryze', 2),
('brambory', 3),
('hranolky', 1);

INSERT INTO Typyjidla (SnidaneObedVecereSvacina) VALUES
('Breakfast'),
('Lunch'),
('Dinner'),
('Snack');

INSERT INTO Uzivatele (Admin, Jmeno, Prijmeni, Heslo) VALUES
(TRUE, 'Admin','User', '$2y$10$eRBtFMw943ugkiEnnLfziuz/sYP7YagkrXjjBPSvHlWu1AtEVKaiW'),
(FALSE, 'Jan','Novák', '$2y$10$R5DYrLHftZV2DCEAzS3U5eYl.8knOwa5laI6PtYCOElDkSM83VgIi');

INSERT INTO Celajidla (HlavnicastjidlaID, UzivatelID, OmackaID, PrilohaID, KategorieID, Pocetsnezenikombinace, TypjidlaID, Obloha, Casjidla)
VALUES
(1, 2, 1, 2, 2, 2, 2, TRUE, '2025-04-20 16:47:00'),
(2, 1, 2, 3, 2, 1, 3, FALSE, '2025-04-20 16:48:00'),
(3, 2, 3, 1, 2, 3, 1, TRUE, '2025-04-20 16:49:00');

INSERT INTO Oblibenejidla (CelejidloID, UzivatelID) VALUES
(1, 2),
(2, 1),
(3, 2);

-- View
CREATE VIEW Jidlauzivatele AS
SELECT c.CelejidloID, c.UzivatelID, h.Nazev AS hNazev, o.Nazev AS oNazev, p.Nazev AS pNazev
FROM Celajidla c
LEFT JOIN Hlavnicastijidel h ON c.HlavnicastjidlaID = h.HlavnicastjidlaID
LEFT JOIN Omacky o ON c.OmackaID = o.OmackaID
LEFT JOIN Prilohy p ON c.PrilohaID = p.PrilohaID
WHERE c.UzivatelID = 2;

DELIMITER //

CREATE FUNCTION GetFullName(UzivatelID INT)
RETURNS VARCHAR(101) -- Combined length of Jmeno + Prijmeni + space
DETERMINISTIC
BEGIN
    DECLARE fullName VARCHAR(101); -- Declare a variable for the full name
    SELECT CONCAT(Jmeno, ' ', Prijmeni) INTO fullName
    FROM Uzivatele
    WHERE UzivatelID = UzivatelID; -- Retrieve Jmeno and Prijmeni based on ID
    RETURN fullName; -- Return the concatenated result
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER ZabranaDuplicityUzivatele
BEFORE INSERT ON Uzivatele
FOR EACH ROW
BEGIN
    DECLARE pocet INT;
    
    SELECT COUNT(*) INTO pocet
    FROM Uzivatele
    WHERE Jmeno = NEW.Jmeno AND Prijmeni = NEW.Prijmeni;
    
    IF pocet > 0 THEN
        SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Uživatel s tímto jménem a příjmením již existuje!';
    END IF;
END //

DELIMITER ;
DELIMITER //

CREATE PROCEDURE PridatCeleJidlo (
    IN p_HlavnicastjidlaID INT,
    IN p_UzivatelID INT,
    IN p_OmackaID INT,
    IN p_PrilohaID INT,
    IN p_KategorieID INT,
    IN p_TypjidlaID INT,
    IN p_Obloha BOOL,
    IN p_Casjidla DATETIME
)
BEGIN
    INSERT INTO Celajidla (
        HlavnicastjidlaID,
        UzivatelID,
        OmackaID,
        PrilohaID,
        KategorieID,
        Pocetsnezenikombinace,
        TypjidlaID,
        Obloha,
        Casjidla
    )
    VALUES (
        p_HlavnicastjidlaID,
        p_UzivatelID,
        p_OmackaID,
        p_PrilohaID,
        p_KategorieID,
        1,
        p_TypjidlaID,
        p_Obloha,
        p_Casjidla
    );
END //

DELIMITER ;


