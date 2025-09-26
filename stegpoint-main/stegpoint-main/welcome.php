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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord - StegPoint</title>
  <link rel="icon" href="Logo_STEG.png" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --blue: #0a2e8a;
      --blue-light: #1d4ed8;
      --red: #dc2626;
      --red-dark: #991b1b;
      --white: #ffffff;
      --glass-bg: rgba(255, 255, 255, 0.1);
      --text-shadow: rgba(0,0,0,0.3);
      --shadow-primary: 0 30px 60px rgba(0,0,0,0.6);
    }

    * {
      box-sizing: border-box;
      margin: 0; padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, var(--blue), var(--red));
      color: var(--white);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 60px 20px 40px;
      transition: background 0.5s ease, color 0.5s ease;
      position: relative;
      overflow-x: hidden;
    }

    /* Background subtle animated texture */
    body::before,
    body::after {
      content: '';
      position: fixed;
      inset: 0;
      z-index: -2;
      background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.06), transparent),
                  radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.05), transparent);
      animation: move-bg 25s linear infinite;
    }
    body::after {
      background-image:
        linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%);
      background-size: 40px 40px;
      opacity: 0.45;
      z-index: -1;
    }

    @keyframes move-bg {
      0% { background-position: 0 0, 0 0; }
      100% { background-position: 1000px 1000px, 1000px 1000px; }
    }

    header {
  width: 100%;
  max-width: 960px;
  background: linear-gradient(90deg, #4a4a4a, #b22222); /* Gris moyen chaud vers rouge brique */
  padding: 20px 30px;
  box-shadow: 0 8px 30px rgba(178, 34, 34, 0.7); /* Ombre rouge brique */
  border-radius: 20px;
  position: relative;
  margin-bottom: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
}


    header img {
      position: absolute;
      left: 30px;
      height: 70px;
      user-select: none;
    }

    header h1 {
      font-weight: 700;
      font-size: 2rem;
      text-shadow: 1px 1px 4px var(--text-shadow);
    }

    #toggle-dark {
      position: absolute;
      right: 30px;
      background: rgba(255,255,255,0.25);
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      cursor: pointer;
      font-size: 1.3rem;
      color: var(--white);
      transition: background 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    #toggle-dark:hover {
      background: rgba(255,255,255,0.45);
    }

    main.container {
      background: var(--glass-bg);
      backdrop-filter: blur(24px);
      border-radius: 25px;
      padding: 50px 40px;
      max-width: 600px;
      box-shadow: var(--shadow-primary);
      text-align: center;
      animation: fadeIn 0.8s ease forwards;
    }

    main.container h2 {
      font-weight: 700;
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: var(--white);
      text-shadow: 2px 2px 6px rgba(0,0,0,0.6);
    }

    main.container p {
      font-size: 1.15rem;
      margin-bottom: 40px;
      color: #eee;
      line-height: 1.5;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
    }

    a.button {
      display: inline-block;
      padding: 14px 32px;
      font-weight: 700;
      font-size: 1.2rem;
      background: linear-gradient(90deg, var(--red), var(--red-dark));
      color: var(--white);
      border-radius: 15px;
      text-decoration: none;
      box-shadow: 0 15px 30px rgba(220, 38, 38, 0.5);
      transition: background 0.3s ease, transform 0.3s ease;
      user-select: none;
    }

    a.button:hover {
      background: linear-gradient(90deg, var(--red-dark), var(--red));
      transform: scale(1.05);
      box-shadow: 0 20px 40px rgba(220, 38, 38, 0.8);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Dark mode */
    .dark-mode {
      background: linear-gradient(135deg, #0f172a, #1e293b);
      color: var(--white);
    }
    .dark-mode header {
      background: #1e293b;
      box-shadow: 0 8px 30px rgba(0,0,0,0.9);
      color: #ddd;
    }
    .dark-mode main.container {
      background: rgba(31, 41, 55, 0.85);
      box-shadow: 0 30px 60px rgba(0,0,0,0.8);
    }
    .dark-mode a.button {
      background: linear-gradient(90deg, #991b1b, #dc2626);
      box-shadow: 0 15px 30px rgba(153, 27, 27, 0.7);
    }

  </style>
</head>
<body>
  <header>
    <img src="Logo_STEG.png" alt="Logo STEG" />
    <h1>StegPoint - Tableau de bord</h1>
    <button id="toggle-dark" aria-label="Basculer mode sombre">🌙</button>
  </header>

  <main class="container">
    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> </h2>
    <p>Merci d'utiliser StegPoint. Vous êtes maintenant connecté.</p>
    <a href="attendance.php" class="button" role="button">Suivant !</a>
  </main>

  <script>
    const toggle = document.getElementById('toggle-dark');
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      toggle.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
    });
  </script>
</body>
</html>
