<?php
// oblibena.php
// Začneme session a načteme konfiguraci pro připojení k databázi
session_start();
$servername = "localhost";
$dbUsername = "abc";
$dbPassword = "heslo";
$dbname     = "jidelnicek";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Chyba při připojení k databázi: " . $conn->connect_error);
}

 if (!isset($_SESSION['user'])){
  
    header("Location: prihlaseni.php");
  exit;
}

// Ověříme, zda je uživatel přihlášen a získáme jeho ID
if (!isset($_SESSION['user']['UzivatelID'])) {
    die("Uživatel není přihlášen.");
}
$userId = $_SESSION['user']['UzivatelID'];

// Připravíme dotaz na pohled s oblíbenými jídly, filtrovaný podle aktuálně přihlášeného uživatele
$sql = "SELECT * FROM OblibeneJidlaPohled WHERE UzivatelID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Chyba při dotazu: " . $conn->error);
}
?>
<?php
 if (!isset($_SESSION['user'])){
  
    header("Location: prihlaseni.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Oblíbená jídla</title>
  <style>
   body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
    header { background-color: #333; color: #fff; text-align: center; padding: 20px 0; }
    .container { max-width: 800px; margin: 0 auto; padding: 20px; }
    .meal { margin-bottom: 20px; }
    h2 { margin-top: 0; }
    select, input[type="text"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; }
    button { background-color: #007bff; color: #fff; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; margin-right: 5px; }
    button:hover { background-color: #0056b3; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
<button class="nav-btn" onclick="window.location.href='jidelnicek.php'">Zpět na jídelníček</button>
  <h1>Oblíbená jídla</h1>
  <table>
    <thead>
      <tr>
    
      
        <th>Hlavní Část Jídla</th>
        <th>Příloha</th>
        <th>Omáčka</th>
        <th>Obloha</th>
        <th>Smazat</th>
        
      </tr>
    </thead>
   
     <tbody>
         <?php while ($row = $result->fetch_assoc()): ?>
        
               
               <td><?php echo htmlspecialchars($row['HlavniCastJidla']); ?></td>
               <td><?php echo htmlspecialchars($row['Priloha']); ?></td>
               <td><?php echo htmlspecialchars($row['Omacka']); ?></td>
               <td><?php echo $row['Obloha'] == 1 ? "Ano" : "Ne"; ?></td>

               <td>
                  <button class="delete-btn" onclick="deleteJidlo(<?php echo $row['OblibenejidloID']; ?>)">Smazat</button>
               </td>
            </tr>
         <?php endwhile; ?>
      </tbody>
  </table>
  <script>
function deleteJidlo(oblibeneJidloId) {
    if (confirm("Opravdu chcete odstranit toto jídlo z oblíbených?")) {
        fetch("backend.php?action=deleteoblibenejidlo", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ OblibenejidloID: oblibeneJidloId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Jídlo bylo úspěšně odstraněno.");
                // Např. můžete odstranit daný řádek z tabulky nebo reloadnout stránku:
                location.reload();
            } else {
                alert("Chyba při mazání: " + (data.error || "Neznámá chyba"));
            }
        })
        .catch(error => {
            console.error("Chyba při volání:", error);
            alert("Nepodařilo se smazat jídlo.");
        });
    }
}
</script>

</body>
</html>
