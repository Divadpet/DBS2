<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Přihlášení</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    form, .button-container {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 40%;
      margin-left: 30%;
      margin-right: 30%;
      text-align: center;
    }
    h2 {
      margin-top: 0;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 3px;
      box-sizing: border-box;
    }
    input[type="submit"] {
      width: 100%;
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 3px;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background-color: #0056b3;
    }
    .buttonb {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 3px;
      cursor: pointer;
      margin-top: 10px;
    }
    .buttonb:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
<?php
session_start();
?>

  <!-- Přihlášení Formulář -->
  <form id="loginForm">
    <h2>Přihlášení</h2>
    <input type="text" id="username" placeholder="Uživatelské jméno" required>
    <input type="password" id="password" placeholder="Heslo" required>
    <input type="submit" value="Přihlásit se">
    <button class="buttonb" onclick="window.location.href='registrace.php'">Registrace</button>
  </form>

  <!-- Tlačítko registrace -->
  
  <script>
  document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault();
    var jmeno = document.getElementById("username").value; // odpovídá poli Jmeno v databázi
    var password = document.getElementById("password").value;

    fetch("backend.php?action=login", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ jmeno: jmeno, password: password })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Neplatné uživatelské jméno nebo heslo');
      }
      return response.text(); // načteme textovou odpověď
    })
    .then(text => {
      try {
        const data = JSON.parse(text); // pokusíme se převést text na JSON
        console.log("Odpověď z backendu:", data);
        if (data.message === "User logged") {
          alert("Přihlášení úspěšné!");
          // Řídíme přesměrování podle role:
          if (data.user && data.user.Admin == 1) {
            // Administrátor
            window.location.href = "uzivatele.php";
          } else {
            // Běžný uživatel
            window.location.href = "jidelnicek.php";
          }
        } else {
          alert("Chyba: " + JSON.stringify(data));
        }
      } catch (error) {
        throw new Error("Invalid JSON received: " + text);
      }
    })
    .catch(error => {
      alert(error.message);
    });
  });
  </script>
</body>
</html>
