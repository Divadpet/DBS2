<?php
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
$username = "root";
$password = "root";
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

switch ($endpoint){

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
    
/*
    case 'register':
        if (!isset($data['username'], $data['password'])) {
            response(400, ["error" => "Wrong json argument"]);
        }

        // Kontrola, jestli už uživatel existuje
        $stmt = $conn->prepare("SELECT * FROM Uzivatele WHERE Jmeno = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            response(409, ["error" => "User already exists"]);
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Uzivatele (Jmeno, Heslo, Admin) VALUES (?, ?, FALSE)");
        $stmt->bind_param("ss", $data['username'], $hash);
        $stmt->execute();
        response(200, ["success" => "User registered successfully"]);
        break;
*/
    case 'register':
        if (!isset($_POST['jmeno'], $_POST['prijmeni'], $_POST['password'])) {
            response(400, ["error" => "Missing form data"]);
        }

        $jmeno = $_POST['jmeno'];
        $prijmeni = $_POST['prijmeni'];
        $password = $_POST['password'];
        $obrazekData = null;

        // Check if user with the same Jmeno and Prijmeni already exists
        $stmt = $conn->prepare("SELECT * FROM Uzivatele WHERE Jmeno = ? AND Prijmeni = ?");
        if (!$stmt) {
            response(500, ["error" => "SQL prepare error: " . $conn->error]);
        }
        $stmt->bind_param("ss", $jmeno, $prijmeni);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            response(409, ["error" => "User already exists"]);
        }

        // Load binary data of the image if uploaded
        if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
            $obrazekData = file_get_contents($_FILES['obrazek']['tmp_name']);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO Uzivatele (Jmeno, Prijmeni, Heslo, Admin, Obrazek) VALUES (?, ?, ?, FALSE, ?)");
        if (!$stmt) {
            response(500, ["error" => "SQL prepare error: " . $conn->error]);
        }
        $stmt->bind_param("ssss", $jmeno, $prijmeni, $hash, $obrazekData);
        $stmt->send_long_data(3, $obrazekData); // 3 corresponds to the last parameter (zero-based index)
        $stmt->execute();

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





    case 'novaData':
        if (!isset($data['TEPLOTA'])) {
            response(400, ["error" => "Missing TEPLOTA"]);
        }
        $cas = datumVeFormatu();
        $stmt = $conn->prepare("INSERT INTO Celajidla (HlavnicastjidlaID, UzivatelID, OmackaID, PrilohaID, KategorieID, Pocetsnezenikombinace, TypjidlaID, Obloha) VALUES (1,1,1,1,1,1,1,0)");
        $stmt->execute();
        response(200, ["success" => "Dummy insert for novaData"]);
        break;

    default:
        response(400, ["error" => "Unknown endpoint"]);
        break;
}

$conn->close();
?>

