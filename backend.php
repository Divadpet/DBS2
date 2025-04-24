<?php
ob_start(); // Zapne output buffering
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Nastavení hlaviček pro CORS a JSON odpovědi
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Odpověď na preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Připojení k databázi (MAMP default hodnoty)
$servername = "localhost";
$username = "abc";
$password = "heslo";
$database = "jidelnicek";
$port = 8889;

$conn = new mysqli($servername, $username, $password, $database, $port);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Načtení dat z requestu
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$endpoint = $_GET['endpoint'] ?? '';

// Pomocná funkce pro odpověď
function response($status, $msg) {
    http_response_code($status);
    echo json_encode($msg);
    exit();
}

// Aktuální datum a čas
function datumVeFormatu() {
    return date("Y-m-d H:i:s");
}
$data = json_decode(file_get_contents('php://input'), true);//tady
$endpoint = isset($_GET['action']) ? $_GET['action'] : '';

function getOrInsertCustom($conn, $table, $column, $id_column, $value) {
    $last_id = null;
    // Zkontroluj, zda hodnota existuje v tabulce
    $stmt = $conn->prepare("SELECT $id_column FROM $table WHERE $column = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();
    
    // Pokud hodnota neexistuje, vlož ji
    if ($stmt->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        
        // Vrátí ID posledního vloženého záznamu
        $last_id = $stmt->insert_id;  // Toto je správné, aby se získalo ID nově vloženého záznamu
    } else {
        // Pokud hodnota existuje, vrátí její ID
        $stmt->bind_result($last_id);
        $stmt->fetch();
    }
    
    return $last_id;  // Tady vracíme ID záznamu, ať už existující nebo nově vložený
}




switch ($endpoint){

    case 'pridatHlavniCast':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nazev']) || empty(trim($data['nazev']))) {
            response(400, "Název hlavního chodu je povinný.");
        }

        $nazev = trim($data['nazev']);

        // Vložení do tabulky hlavnicastijidel
        $stmt = $conn->prepare("INSERT INTO hlavnicastijidel (Nazev, Pocetsnezeni) VALUES (?, 0)");
        if (!$stmt) {
            response(500, "Chyba při přípravě SQL: " . $conn->error);
        }
        $stmt->bind_param("s", $nazev);
        if ($stmt->execute()) {
            response(200, "Hlavní chod '$nazev' byl úspěšně přidán.");
        } else {
            response(500, "Chyba při vkládání hlavního chodu: " . $stmt->error);
        }
        break;

    case 'pridatPriloha':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nazev']) || empty(trim($data['nazev']))) {
            response(400, "Název přílohy je povinný.");
        }

        $nazev = trim($data['nazev']);

        // Vložení do tabulky prilohy
        $stmt = $conn->prepare("INSERT INTO prilohy (Nazev, Pocetsnezeni) VALUES (?, 0)");
        if (!$stmt) {
            response(500, "Chyba při přípravě SQL: " . $conn->error);
        }
        $stmt->bind_param("s", $nazev);
        if ($stmt->execute()) {
            response(200, "Příloha '$nazev' byla úspěšně přidána.");
        } else {
            response(500, "Chyba při vkládání přílohy: " . $stmt->error);
        }
        break;

    case 'pridatOmacka':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nazev']) || empty(trim($data['nazev']))) {
            response(400, "Název omáčky je povinný.");
        }

        $nazev = trim($data['nazev']);

        // Vložení do tabulky omacky
        $stmt = $conn->prepare("INSERT INTO omacky (Nazev, Pocetsnezeni) VALUES (?, 0)");
        if (!$stmt) {
            response(500, "Chyba při přípravě SQL: " . $conn->error);
        }
        $stmt->bind_param("s", $nazev);
        if ($stmt->execute()) {
            response(200, "Omáčka '$nazev' byla úspěšně přidána.");
        } else {
            response(500, "Chyba při vkládání omáčky: " . $stmt->error);
        }
        break;
     
    case 'getData':
        // Nejprve ověříme, zda je uživatel přihlášen:
        if (!isset($_SESSION['user']['UzivatelID'])) {
            response(401, ["error" => "User not logged in."]);
        }
        $userId = $_SESSION['user']['UzivatelID'];
    
        $response = [];
    
        // Načtení dat pro Hlavní chod
        $hlavniChody = [];
        $sql = "SELECT HlavnicastjidlaID, Nazev FROM Hlavnicastijidel";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $hlavniChody[] = $row;
            }
            $result->free();
        }
        $response['hlavniChody'] = $hlavniChody;
    
        // Načtení dat pro Přílohy
        $prilohy = [];
        $sql = "SELECT PrilohaID, Nazev FROM Prilohy";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $prilohy[] = $row;
            }
            $result->free();
        }
        $response['prilohy'] = $prilohy;
    
        // Načtení dat pro Omáčky
        $omacky = [];
        $sql = "SELECT OmackaID, Nazev FROM Omacky";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $omacky[] = $row;
            }
            $result->free();
        }
        $response['omacky'] = $omacky;
    
        // Načtení dat pro Uložená jídla (JOIN dotaz)
        // Zde upravíme dotaz, aby se načetla pouze jídla zadaná přihlášeným uživatelem
        $jidla = [];
        $sql = "SELECT * FROM jidlauzivatele 
                WHERE UzivatelID = ? ORDER BY Casjidla DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            response(500, ["error" => "SQL prepare error: " . $conn->error]);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $jidla[] = $row;
        }
        $stmt->close();
        $response['jidla'] = $jidla;
    
        // Zakóduj data do JSON a odešli odpověď
        $jsonOutput = json_encode($response);
        if ($jsonOutput === false) {
            echo json_encode(["error" => "JSON encoding error: " . json_last_error_msg()]);
            exit;
        }
        echo $jsonOutput;
        exit;
        
    
        

    

    


       
   
        case 'novaData':
            if (!isset($_SESSION['user']['UzivatelID'])) {
                response(401, ["error" => "Uživatel není přihlášen"]);
            }
        
            $uzivatelID = $_SESSION['user']['UzivatelID'];
            $data = json_decode(file_get_contents('php://input'), true);
        
            if (!isset($data['hlavniChod']) || empty($data['hlavniChod'])) {
                response(400, ["error" => "Hlavní chod nebyl zadán"]);
            }
        
            // Získání ID hlavního chodu
            $hlavniChod = ($data['hlavniChod'] === "Custom")
            ? getOrInsertCustom($conn, "Hlavnicastijidel", "Nazev", "HlavnicastjidlaID", $data['custom_hlavni_chod'])
            : intval($data['hlavniChod']);
        
            // Získání ID přílohy
            $priloha = ($data['priloha'] === "Custom")
                ? getOrInsertCustom($conn, "Prilohy", "Nazev", "PrilohaID", $data['custom_priloha'])
                : (isset($data['priloha']) ? intval($data['priloha']) : null);
        
            // Získání ID omáčky
            $omacka = ($data['omacka'] === "Custom")
                ? getOrInsertCustom($conn, "Omacky", "Nazev", "OmackaID", $data['custom_omacka'])
                : (isset($data['omacka']) ? intval($data['omacka']) : null);
        
            // Ostatní data
            $obloha = isset($data['obloha']) ? intval($data['obloha']) : 0;
            $casjidla = isset($data['casjidla']) ? $data['casjidla'] : date("Y-m-d H:i:s");
            $typjidla = isset($data['typjidla']) ? intval($data['typjidla']) : 1; // např. snídaně = 1
        
            $stmt = $conn->prepare("CALL PridatCeleJidlo(?, ?, ?, ?, 1, ?, ?, ?)");
            if (!$stmt) {
                response(500, ["error" => "SQL příprava selhala: " . $conn->error]);
            }
        
            $stmt->bind_param("iiiiiis", $hlavniChod, $uzivatelID, $omacka, $priloha, $typjidla, $obloha, $casjidla);
            if (!$stmt->execute()) {
                response(500, ["error" => "Vložení dat selhalo: " . $stmt->error]);
            }
        
            response(200, ["success" => true, "data" => [
                "hlavni" => $data['custom_hlavni_chod'] ?? $hlavniChod,
                "priloha" => $data['custom_priloha'] ?? $priloha,
                "omacka" => $data['custom_omacka'] ?? $omacka,
                "casjidla" => $casjidla
            ]]);
            break;
        

            
           
    

            case 'deleteoblibenejidlo':
                // Ověření, zda je uživatel přihlášen
                if (!isset($_SESSION['user']['UzivatelID'])) {
                    response(401, ["error" => "User not logged in"]);
                }
                $userId = $_SESSION['user']['UzivatelID'];
                
                // Načtení vstupních dat – očekává se JSON s klíčem OblibenejidloID
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['OblibenejidloID'])) {
                    response(400, ["error" => "Missing OblibenejidloID"]);
                }
                $oblibeneJidloID = intval($data['OblibenejidloID']);
                
                // Připravíme prepared statement pro smazání záznamu z tabulky Oblibenejidla,
                // kde záznam patří přihlášenému uživateli
                $stmt = $conn->prepare("DELETE FROM Oblibenejidla WHERE OblibenejidloID = ? AND UzivatelID = ?");
                if (!$stmt) {
                    response(500, ["error" => "SQL prepare error: " . $conn->error]);
                }
                $stmt->bind_param("ii", $oblibeneJidloID, $userId);
                
                if (!$stmt->execute()) {
                    response(500, ["error" => "Deletion failed: " . $stmt->error]);
                }
                
                response(200, ["success" => "Oblíbené jídlo bylo úspěšně smazáno"]);
                break;
            


                case 'deletejidlo':
                    // Ověření, zda je uživatel přihlášen
                    if (!isset($_SESSION['user']['UzivatelID'])) {
                        response(401, ["error" => "User not logged in"]);
                    }
                    $userId = $_SESSION['user']['UzivatelID'];
                    
                    // Načtení vstupních dat – očekáváme JSON s klíčem CelejidloID
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['CelejidloID'])) {
                        response(400, ["error" => "Missing CelejidloID"]);
                    }
                    $celejidloID = intval($data['CelejidloID']);
                    
                    // Připravíme prepared statement pro smazání záznamu z tabulky Celajidla,
                    // kde záznam patří přihlášenému uživateli
                    $stmt = $conn->prepare("DELETE FROM Celajidla WHERE CelejidloID = ? AND UzivatelID = ?");
                    if (!$stmt) {
                        response(500, ["error" => "SQL prepare error: " . $conn->error]);
                    }
                    $stmt->bind_param("ii", $celejidloID, $userId);
                    
                    if (!$stmt->execute()) {
                        response(500, ["error" => "Deletion failed: " . $stmt->error]);
                    }
                    
                    response(200, ["success" => "Jídlo bylo úspěšně odstraněno z databáze"]);
                    break;


                
                    case 'pridatoblibenejidlo':
                        // Ověření, zda je uživatel přihlášen
                        if (!isset($_SESSION['user']['UzivatelID'])) {
                            response(401, ["error" => "User not logged in"]);
                        }
                        $userId = $_SESSION['user']['UzivatelID'];
                        
                        // Načtení vstupních dat – očekáváme JSON s klíčem CelejidloID,
                        // což je ID jídla z tabulky Celajidla, které chceme přidat do oblíbených.
                        $data = json_decode(file_get_contents('php://input'), true);
                        if (!isset($data['CelejidloID'])) {
                            response(400, ["error" => "Missing CelejidloID"]);
                        }
                        $celejidloID = intval($data['CelejidloID']);
                        
                        // Příprava volání uložené procedury pro přidání oblíbeného jídla
                        $stmt = $conn->prepare("CALL PridatOblibeneJidlo(?, ?)");
                        if (!$stmt) {
                            response(500, ["error" => "SQL prepare error: " . $conn->error]);
                        }
                        
                        // Vázání parametrů – první parametr je ID jídla, druhý ID uživatele
                        $stmt->bind_param("ii", $celejidloID, $userId);
                        
                        // Provedeme volání procedury
                        if (!$stmt->execute()) {
                            response(500, ["error" => "Procedure execution error: " . $stmt->error]);
                        }
                        
                        // Vrácení úspěšné odpovědi
                        response(200, ["success" => "Jídlo bylo úspěšně přidáno mezi oblíbená"]);
                        break;
                    
            
        
        
    
    
    
    
     case 'deleteUser':
            // Kontrola, že je metoda POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                response(405, ["error" => "Method not allowed"]);
            }
    
            if (!isset($_SESSION['user']) || !$_SESSION['user']['Admin']) {
                response(401, ["error" => "Unauthorized"]);
            }
    
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['UzivatelID'])) {
                response(400, ["error" => "Missing User ID"]);
            }
    
            $userId = intval($data['UzivatelID']);
    
            // Nepovolit smazání vlastního účtu
            if ($userId === $_SESSION['user']['UzivatelID']) {
                response(400, ["error" => "Nelze smazat vlastní účet"]);
            }
    
            error_log("Mazání uživatele s ID: " . $userId);
    
            $stmt = $conn->prepare("CALL DeleteUserProc(?)");
            if (!$stmt) {
                response(500, ["error" => "SQL prepare error: " . $conn->error]);
            }
            $stmt->bind_param("i", $userId);
    
            if ($stmt->execute()) {
                response(200, ["success" => true]);
            } else {
                response(500, ["error" => "Chyba při mazání uživatele"]);
            }
            break;

            

                case 'PocetJidelUzivatele':
                    if (!isset($_SESSION['user']['UzivatelID'])) {
                        response(401, ["error" => "User not logged in"]);
                    }
            
                    if (!$conn) {
                        response(500, ["error" => "Database connection missing"]);
                    }
            
                    $userId = $_SESSION['user']['UzivatelID'];
            
                    // Prepared statements don't work well with stored functions, so use plain query
                    $query = "SELECT SpoctiPocetJidel(" . (int)$userId . ") AS mealCount";
                    $result = $conn->query($query);
            
                    if (!$result) {
                        error_log("Query failed: " . $conn->error);
                        response(500, ["error" => "Query failed", "details" => $conn->error]);
                    }
            
                    $row = $result->fetch_assoc();
                    response(200, ["mealCount" => $row['mealCount'] ?? ""]);
                    break;

                    case 'PosledniJidlo':
                        if (!isset($_SESSION['user']['UzivatelID'])) {
                            response(401, ["error" => "User not logged in"]);
                        }
                
                        if (!$conn) {
                            response(500, ["error" => "Database connection missing"]);
                        }
                
                        $userId = $_SESSION['user']['UzivatelID'];
                
                        // Prepared statements don't work well with stored functions, so use plain query
                        $query = "SELECT PosledniCasJidla(" . (int)$userId . ") AS lastMeal";
                        $result = $conn->query($query);
                
                        if (!$result) {
                            error_log("Query failed: " . $conn->error);
                            response(500, ["error" => "Query failed", "details" => $conn->error]);
                        }
                
                        $row = $result->fetch_assoc();
                        response(200, ["lastMeal" => $row['lastMeal'] ?? ""]);
                        break;
                
    
       
    
    

   

            case 'getFullName':
                if (!isset($_SESSION['user']['UzivatelID'])) {
                    response(401, ["error" => "User not logged in"]);
                }
        
                if (!$conn) {
                    response(500, ["error" => "Database connection missing"]);
                }
        
                $userId = $_SESSION['user']['UzivatelID'];
        
                // Prepared statements don't work well with stored functions, so use plain query
                $query = "SELECT GetFullName(" . (int)$userId . ") AS fullName";
                $result = $conn->query($query);
        
                if (!$result) {
                    error_log("Query failed: " . $conn->error);
                    response(500, ["error" => "Query failed", "details" => $conn->error]);
                }
        
                $row = $result->fetch_assoc();
                response(200, ["fullName" => $row['fullName'] ?? ""]);
                break;

    case 'login':
        // Ověření vstupních dat
        if (!isset($data['jmeno'], $data['password'])) { // Updated key from 'username' to 'jmeno'
            response(400, ["error" => "Wrong json argument"]);
        }
    
        $jmeno = $data['jmeno']; // Match Jmeno field
        $password = $data['password'];
    
        // Připravíme dotaz do tabulky Uzivatele, kde porovnáváme sloupec Jmeno
        $stmt = $conn->prepare("SELECT Jmeno, Heslo, UzivatelID, Admin, Prijmeni FROM Uzivatele WHERE Jmeno = ?");
        if (!$stmt) {
            response(500, ["error" => "SQL prepare error: " . $conn->error]);
        }
        $stmt->bind_param("s", $jmeno);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        error_log("User: " . print_r($user, true));
    
        // Ověříme, zda uživatel existuje a heslo odpovídá
        if ($user && password_verify($password, $user['Heslo'])) {
            // Odstraníme heslo z uživatelských dat, než je odešleme klientovi
            unset($user['Heslo']);
            // Uložíme uživatele do session, pokud to potřebujeme pro budoucí práci
            $_SESSION['user'] = [
                'UzivatelID' => $user['UzivatelID'],
                'Admin' => $user['Admin'], 
                'Jmeno' => $user['Jmeno'], 
                'Prijmeni' => $user['Prijmeni']
            ];
            
            // Vrátíme odpověď, zde používáme status code 200 (OK)
            response(200, ["message" => "User logged", "user" => $user]);
        } else {
            response(401, ["error" => "Invalid username or password"]);
        }
        break;
    
        case 'register':
            // Ověříme, že jsou odeslána data
            if (!isset($_POST['jmeno'], $_POST['prijmeni'], $_POST['password'])) {
                response(400, ["error" => "Missing form data"]);
            }
        
            $jmeno = $_POST['jmeno'];
            $prijmeni = $_POST['prijmeni'];
            $password = $_POST['password'];
            $obrazekData = null;
        
            
        
            // Load image data if uploaded
            if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
                $obrazekData = file_get_contents($_FILES['obrazek']['tmp_name']);
            }
        
            $hash = password_hash($password, PASSWORD_DEFAULT);
        
            // Prepared statement pro INSERT do tabulky Uzivatele
            $stmt = $conn->prepare("INSERT INTO Uzivatele (Jmeno, Prijmeni, Heslo, Admin, Obrazek) VALUES (?, ?, ?, FALSE, ?)");
            if (!$stmt) {
                response(500, ["error" => "SQL prepare error: " . $conn->error]);
            }
            // Předáváme data jako stringy; například u binárních dat můžeš použít send_long_data
            $stmt->bind_param("ssss", $jmeno, $prijmeni, $hash, $obrazekData);
            if ($obrazekData !== null) {
                $stmt->send_long_data(3, $obrazekData); // 3 odpovídá čtvrtému parametru (indexováno od nuly)
            }
        
            if (!$stmt->execute()) {
                // Pokud při vložení dojde k chybě, můžeme zjistit, zda se jedná o duplicitní záznam
                // Kontrola pomocí chybové zprávy z triggeru
                if (strpos($conn->error, 'Uživatel s tímto jménem a příjmením již existuje!') !== false) {
                    response(409, ["error" => "Uživatel s tímto jménem a příjmením již existuje!"]);
                } else {
                    response(500, ["error" => "Execution error: " . $conn->error]);
                }
            }
        
            response(200, ["message" => "User registered"]);
            break;
        



    case 'getAllMeals':
        $result = $conn->query("SELECT * FROM Celajidla");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        response(200, $rows); // Odeslání dat přímo jako pole
        break;

    case 'saveMeal':
        if (!$data || !is_array($data)) {
            response(400, ["error" => "Missing meal data"]);
        }
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $types = str_repeat("s", count($data));
        $stmt = $conn->prepare("INSERT INTO Celajidla ($columns) VALUES ($placeholders)");
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
        response(200, ["success" => "Meal saved successfully"]);
        break;

    case 'deleteMeal':
        if (!isset($data['mealId'])) {
            response(400, ["error" => "Wrong json argument"]);
        }
        $stmt = $conn->prepare("DELETE FROM Celajidla WHERE CelejidloID = ?");
        $stmt->bind_param("i", $data['mealId']);
        $stmt->execute();
        response(200, ["success" => "Meal deleted successfully"]);
        break;
        
        case 'updateMeal': 
        if (!isset($data['mealId'], $data['newMeal'])) { response(400, ["error" => "Wrong json argument"]); 
        } 
        $m = $data['newMeal'];
         $stmt = $conn->prepare("UPDATE Celajidla SET HlavnicastjidlaID=?, UzivatelID=?, OmackaID=?, PrilohaID=?, KategorieID=?, Pocetsnezenikombinace=?, TypjidlaID=?, Obloha=? WHERE CelejidloID=?");
          $stmt->bind_param("iiiiiiiii", $m['HlavnicastjidlaID'], $m['UzivatelID'], $m['OmackaID'], $m['PrilohaID'], $m['KategorieID'], $m['Pocetsnezenikombinace'], $m['TypjidlaID'], $m['Obloha'], $data['mealId']); $stmt->execute(); if ($stmt->error) { response(200, ["success" => "Meal updated successfully"]);} break;

  case 'newMeal':
    if (!isset($data['hlavniChod'], $data['priloha'], $data['omacka'])) {
        response(400, ["error" => "Wrong json argument"]);
    }
    $obloha = !empty($data['obloha']) ? 1 : 0;

    // Pomocná funkce: vrátí existující ID nebo vloží nový záznam a vrátí jeho ID.
    // Upravili jsme INSERT dotaz tak, že vynecháváme auto_increment sloupec.
    function getOrCreateEntry($conn, $table, $idColumn, $nameColumn, $value) {
        // Zkontrolujeme, zda již záznam s daným názvem existuje
        $stmt = $conn->prepare("SELECT $idColumn FROM $table WHERE $nameColumn = ?");
        if(!$stmt){
            error_log("Prepare SELECT error: " . $conn->error);
            return false;
        }
        $stmt->bind_param("s", $value);
        if(!$stmt->execute()){
            error_log("Execute SELECT error: " . $stmt->error);
            return false;
        }
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            return $row[$idColumn];
        } else {
            // Vložíme nový záznam – auto_increment sloupec vynecháme
            $stmt = $conn->prepare("INSERT INTO $table ($nameColumn, Pocetsnezeni) VALUES (?, 0)");
            if(!$stmt){
                error_log("Prepare INSERT error: " . $conn->error);
                return false;
            }
            $stmt->bind_param("s", $value);
            if(!$stmt->execute()){
                error_log("Execute INSERT error: " . $stmt->error);
                return false;
            }
            return $conn->insert_id;
        }
    }
    
    // Zpracování hlavního chodu
    $hlavniChodVal = $data['hlavniChod'];
    if (is_numeric($hlavniChodVal)) {
        $hlavniChodID = intval($hlavniChodVal);
    } else {
        $hlavniChodID = getOrCreateEntry($conn, "Hlavnicastijidel", "HlavnicastjidlaID", "Nazev", $hlavniChodVal);
        if($hlavniChodID === false) {
            response(500, ["error" => "Chyba při zpracování hlavního chodu."]);
        }
    }
    
    // Zpracování přílohy
    $prilohaVal = $data['priloha'];
    if (is_numeric($prilohaVal)) {
        $prilohaID = intval($prilohaVal);
    } else {
        $prilohaID = getOrCreateEntry($conn, "Prilohy", "PrilohaID", "Nazev", $prilohaVal);
        if($prilohaID === false) {
            response(500, ["error" => "Chyba při zpracování přílohy."]);
        }
    }
    
    // Zpracování omáčky
    $omackaVal = $data['omacka'];
    if (is_numeric($omackaVal)) {
        $omackaID = intval($omackaVal);
    } else {
        $omackaID = getOrCreateEntry($conn, "Omacky", "OmackaID", "Nazev", $omackaVal);
        if($omackaID === false) {
            response(500, ["error" => "Chyba při zpracování omáčky."]);
        }
    }
    
    // Vložení záznamu do tabulky Celajidla s číselnými hodnotami
    $stmt = $conn->prepare("INSERT INTO Celajidla (HlavnicastjidlaID, UzivatelID, OmackaID, PrilohaID, KategorieID, Pocetsnezenikombinace, TypjidlaID, Obloha) VALUES (?, 1, ?, ?, 1, 1, 1, ?)");
    if(!$stmt){
        response(500, ["error" => "Prepare Celajidla error: " . $conn->error]);
    }
    $stmt->bind_param("iiii", $hlavniChodID, $omackaID, $prilohaID, $obloha);
    if(!$stmt->execute()){
        response(500, ["error" => "SQL error: " . $stmt->error]);
    }
    
    response(200, ["success" => "Meal created successfully"]);
    break;





   
}

$conn->close();
?>