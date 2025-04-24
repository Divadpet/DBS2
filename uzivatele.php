<?php
session_start();

// Připojení k databázi – můžeš mít vlastní konfigurační kód nebo require_once 'dbconfig.php'
$servername = "localhost";
$dbUsername = "abc";
$dbPassword = "heslo";
$dbname     = "jidelnicek";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Chyba při připojení k databázi: " . $conn->connect_error);
}

// Dotaz na view VypisUzivatelu
$query = "SELECT UzivatelID, Jmeno, Prijmeni, Obrazek FROM VypisUzivatelu";
$result = $conn->query($query);
if (!$result) {
    die("Chyba při načítání dat: " . $conn->error);
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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Správa uživatelů</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         margin: 20px;
      }
      table {
         width: 100%;
         border-collapse: collapse;
      }
      th, td {
         border: 1px solid #ccc;
         padding: 10px;
         text-align: left;
      }
      tr:nth-child(even) {
         background-color: #f2f2f2;
      }
      .delete-btn {
         background-color: #e74c3c;
         color: white;
         border: none;
         padding: 6px 12px;
         border-radius: 4px;
         cursor: pointer;
      }
      .delete-btn:hover {
         background-color: #c0392b;
      }
      .user-img {
         width: 70px;
         height: auto;
      }
      button { background-color: #007bff; color: #fff; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; margin-right: 5px; }
    button:hover { background-color: #0056b3; }
   </style>
</head>
<body>
 <button class="nav-btn" onclick="window.location.href='jidelnicek.php'">Zpět na jídelníček</button>
   <h1>Správa uživatelů</h1>
   <table>
      <thead>
         <tr>
            <th>Obrázek</th>
            <th>Jméno</th>
            <th>Příjmení</th>
            <th>Akce</th>
         </tr>
      </thead>
      <tbody>
         <?php while ($user = $result->fetch_assoc()): ?>
            <tr id="user-<?php echo $user['UzivatelID']; ?>">
               <td>
                  <?php
                  if (!empty($user['Obrazek'])) {
                      // Předpokládáme, že obrázek je uložen jako JPEG – v případě potřeby změň MIME typ
                      $imgData = base64_encode($user['Obrazek']);
                      echo '<img src="data:image/jpeg;base64,' . $imgData . '" alt="Obrázek uživatele" class="user-img">';
                  } else {
                      echo 'Bez obrázku';
                  }
                  ?>
               </td>
               <td><?php echo htmlspecialchars($user['Jmeno']); ?></td>
               <td><?php echo htmlspecialchars($user['Prijmeni']); ?></td>
               <td>
                  <button class="delete-btn" onclick="deleteUser(<?php echo $user['UzivatelID']; ?>)">Smazat</button>
               </td>
            </tr>
         <?php endwhile; ?>
      </tbody>
   </table>

   <script>
      function deleteUser(userId) {
         if (confirm("Opravdu chcete smazat tohoto uživatele?")) {
            fetch("backend.php?action=deleteUser", {
               method: "POST",
               headers: {
                  "Content-Type": "application/json"
               },
               body: JSON.stringify({ UzivatelID: userId })
            })
            .then(response => {
               console.log("Raw response:", response);
               if (!response.ok) {
                  console.error("Response není OK, status code:", response.status);
               }
               return response.json();
            })
            .then(data => {
               console.log("Data vrácená ze serveru:", data);
               if (data.success) {
                  // Odeber řádek smazaného uživatele z tabulky
                  const row = document.getElementById("user-" + userId);
                  if (row) row.remove();
               } else {
                  alert("Chyba při mazání uživatele: " + (data.error || "neznámá chyba"));
               }
            })
            .catch(error => {
               console.error("Chyba při fetch:", error);
               alert("Chyba: " + error);
            });
         }
      }
   </script>
</body>
</html>
<?php
$conn->close();
?>
