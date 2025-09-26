<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord - StegPoint</title>
  <link rel="icon" href="Logo_STEG.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --blue: #0b3d91;
      --blue-light: #1e66c1;
      --white: #ffffff;
      --gray-light: #f4f6f8;
      --red: #e63946;
      --red-dark: #c91f2c;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, var(--blue), #071d42);
      color: var(--white);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
      padding-top: 60px;
      transition: background 0.5s ease, color 0.5s ease;
    }

    header {
      width: 100%;
      background: var(--blue-light);
      color: white;
      padding: 20px 0;
      text-align: center;
      position: relative;
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    header img {
      position: absolute;
      top: 12px;
      left: 20px;
      height: 60px;
    }

    #toggle-dark {
      position: absolute;
      top: 20px;
      right: 20px;
      background: var(--white);
      border: none;
      border-radius: 8px;
      padding: 8px 12px;
      cursor: pointer;
      font-weight: bold;
      color: var(--blue);
    }

    .container {
      background: var(--white);
      color: #333;
      border-radius: 15px;
      padding: 40px;
      width: 90%;
      max-width: 600px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      animation: fadeIn 0.6s ease-in-out;
    }

    h1, h2 {
      color: var(--blue);
    }

    a.button {
      display: inline-block;
      margin-top: 25px;
      padding: 12px 24px;
      background: var(--red);
      color: white;
      text-decoration: none;
      border: none;
      border-radius: 10px;
      transition: background 0.3s ease;
      font-weight: bold;
      cursor: pointer;
    }

    a.button:hover {
      background: var(--red-dark);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Mode sombre */
    .dark-mode {
      background: #1c1c1c;
      color: var(--white);
    }

    .dark-mode .container {
      background: #2a2a2a;
      color: var(--white);
    }

    .dark-mode a.button {
      background: var(--red-dark);
    }
  </style>
</head>
<body>
  <header>
    <img src="Logo_STEG.png" alt="Logo STEG">
    <h1>StegPoint - Tableau de bord</h1>
    <button id="toggle-dark">🌙</button>
  </header>
   <div class="container">
    

<hr style="margin: 30px 0;">

<!-- ... garde le reste de ton code intact au-dessus ... -->

<h2>Rechercher une feuille de présence</h2>
<form method="POST" action="attendance.php">

  <!-- Choix du type de recherche -->
  <div style="text-align: left; margin-bottom: 20px;">
    <label>Mode de recherche :</label><br>
    <select id="search_mode" name="search_mode" style="width: 100%; padding: 10px; border-radius: 8px;">
      <option value="department">Par département</option>
      <option value="employee">Par ID et nom</option>
      <option value="date">Par date</option>
    </select>
  </div>

  <!-- Recherche par département -->
  <div id="department_fields" style="margin-bottom: 20px;">
    <label for="department">Département</label><br>
    <select id="department" name="department" style="width: 100%; padding: 10px; border-radius: 8px;">
      <option value="">-- Choisir --</option>
      <option value="Centrale étape A-B">Centrale étape A-B</option>
      <option value="Centrale étape C">Centrale étape C</option>
      <option value="Centrale étape D">Centrale étape D</option>
      <option value="Division comptabilité et finance">Division comptabilité et finance</option>
      <option value="Division conduite A-B">Division conduite A-B</option>
      <option value="Division conduite C">Division conduite C</option>
      <option value="Division conduite D">Division conduite D</option>
      <option value="Division gestion contrats de maintenance">Division gestion contrats de maintenance</option>
      <option value="Division gestion de Stocks">Division gestion de Stocks</option>
      <option value="Division gestion technique et performances A-B">Division gestion technique et performances A-B</option>
      <option value="Division gestion technique et performances C">Division gestion technique et performances C</option>
      <option value="Division gestion technique et performances D">Division gestion technique et performances D</option>
      <option value="Division informatique">Division informatique</option>
      <option value="Division maintenance courante A-B">Division maintenance courante A-B</option>
      <option value="Division maintenance courante C">Division maintenance courante C</option>
      <option value="Division maintenance courante D">Division maintenance courante D</option>
      <option value="Division production, traitement d'eau et chimie A-B">Division production, traitement d'eau et chimie A-B</option>
      <option value="Division production, traitement d'eau et chimie C">Division production, traitement d'eau et chimie C</option>
      <option value="Division production, traitement d'eau et chimie D">Division production, traitement d'eau et chimie D</option>
      <option value="Division ressource humaines">Division ressource humaines</option>
      <option value="Division suivi des performances et éfficacité énergétique">Division suivi des performances et éfficacité énergétique</option>
      <option value="Division sécurité et environnement">Division sécurité et environnement</option>
      <option value="Département maintenance">Département maintenance</option>
      <option value="GROUPEMENT DE PRODUCTION DE SOUSSE">GROUPEMENT DE PRODUCTION DE SOUSSE</option>
      <option value="Service logistique">Service logistique</option>
    </select>
  </div>

  <!-- Recherche par employé -->
  <div id="employee_fields" style="display: none; margin-bottom: 20px;">
    <label for="employee_id">ID Employé</label><br>
    <input type="text" id="employee_id" name="employee_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;"><br><br>

    <label for="employee_name">Nom</label><br>
    <input type="text" id="employee_name" name="employee_name" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
  </div>

  <!-- Recherche par date -->
  <div id="date_fields" style="display: none; margin-bottom: 20px;">
    <label for="attendance_date">Date</label><br>
    <input type="date" id="attendance_date" name="attendance_date" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
  </div>

  <input type="submit" value="Rechercher" class="button">
</form>


  </div>
  <script>
    const toggle = document.getElementById('toggle-dark');
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      toggle.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
    });
  </script>
  <script>
  const searchMode = document.getElementById('search_mode');
  const deptFields = document.getElementById('department_fields');
  const empFields = document.getElementById('employee_fields');
  const dateFields = document.getElementById('date_fields');

  function updateFields() {
    deptFields.style.display = searchMode.value === 'department' ? 'block' : 'none';
    empFields.style.display = searchMode.value === 'employee' ? 'block' : 'none';
    dateFields.style.display = searchMode.value === 'date' ? 'block' : 'none';
  }

  searchMode.addEventListener('change', updateFields);
  window.addEventListener('DOMContentLoaded', updateFields);
</script>

</body>
</html>
