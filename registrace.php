<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrace</title>
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
    form {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      text-align: center;
    }
    h2 {
      margin-top: 0;
    }
    input[type="text"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 3px;
      box-sizing: border-box;
    }
    input[type="submit"] {
      width: 100%;
      background-color: #0056b3;
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
ob_start();
?>
  <form id="registerForm" enctype="multipart/form-data">
    <h2>Registrace</h2>
    <input type="text" id="jmeno" placeholder="Jméno" required>
    <input type="text" id="prijmeni" placeholder="Příjmení" required>
    <input type="password" id="password" placeholder="Heslo" required>
    <input type="file" id="obrazek" accept="image/*">
    <input type="submit" value="Registrovat se">
    <button class="buttonb" onclick="window.location.href='prihlaseni.php'">Prihlášení</button>
  </form>

  <script>
    document.getElementById("registerForm").addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData();
      formData.append("jmeno", document.getElementById("jmeno").value);
      formData.append("prijmeni", document.getElementById("prijmeni").value);
      formData.append("password", document.getElementById("password").value);
      formData.append("obrazek", document.getElementById("obrazek").files[0]);

      fetch("backend.php?action=register", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Registrace se nezdařila");
          }
          return response.json();
        })
        .then((data) => {
          console.log("Odpověď z backendu:", data);
          if (data.message === "User registered") {
            alert("Registrace úspěšná!");
            window.location.href = "jidelnicek.php";
          } else {
            alert("Chyba: " + JSON.stringify(data));
          }
        })
        .catch((error) => {
          alert(error.message);
        });
    });
  </script>
</body>
</html>
