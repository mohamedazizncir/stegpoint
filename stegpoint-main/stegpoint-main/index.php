<?php
session_start();
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion - StegPoint</title>
  <link rel="icon" href="Logo_STEG.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pbQg1OQ+7YoML+4MXYdt8QwoFf1A4D5q/hw5wKBAqRwZBkJHJxP2we5t5RY5vMZdXz5ZqlFApU+hN8BJzJZG1Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <style>
    :root {
      --blue: #0a2e8a;
      --blue-light: #1d4ed8;
      --red: #dc2626;
      --red-dark: #991b1b;
      --white: #ffffff;
      --glass-bg: rgba(255, 255, 255, 0.08);
      --input-bg: rgba(255, 255, 255, 0.1);
      --error-color: #ff4d4f;
      --primary-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, var(--blue), var(--red));
      color: var(--white);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .background-overlay::before,
    .background-overlay::after {
      content: '';
      position: fixed;
      inset: 0;
      z-index: -2;
      background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.05), transparent),
                  radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.04), transparent);
      animation: move-bg 20s infinite linear;
    }

    .background-overlay::after {
      background-image: 
        linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%);
      background-size: 40px 40px;
      opacity: 0.5;
      z-index: -1;
    }

    @keyframes move-bg {
      0% { background-position: 0 0, 0 0; }
      100% { background-position: 1000px 1000px, 1000px 1000px; }
    }

    .login-card {
      background: var(--glass-bg);
      backdrop-filter: blur(18px);
      padding: 40px 30px;
      border-radius: 20px;
      width: 100%;
      max-width: 440px;
      box-shadow: var(--primary-shadow);
      text-align: center;
      animation: fadeIn 0.8s ease;
      position: relative;
    }

    .login-card img {
      height: 70px;
      margin-bottom: 20px;
    }

    .login-card h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
    }

    .input-floating {
      position: relative;
      margin-bottom: 20px;
    }

    .input-floating input {
      width: 100%;
      padding: 14px 12px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 10px;
      background: var(--input-bg);
      color: white;
      font-size: 1rem;
    }

    .input-floating label {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      font-size: 1rem;
      color: #aaa;
      transition: 0.3s ease;
      pointer-events: none;
    }

    .input-floating input:focus + label,
    .input-floating input:not(:placeholder-shown) + label {
      top: -10px;
      left: 10px;
      font-size: 0.75rem;
      background: rgba(0, 0, 0, 0.6);
      padding: 0 6px;
      border-radius: 4px;
      color: var(--white);
    }

    .login-card input[type="submit"] {
      width: 100%;
      padding: 12px;
      background: linear-gradient(to right, var(--red-dark), var(--red));
      border: none;
      border-radius: 10px;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s;
    }

    .login-card input[type="submit"]:hover {
      background: linear-gradient(to right, var(--red), var(--red-dark));
      transform: scale(1.02);
    }

    .error-message {
      background: rgba(255, 0, 0, 0.15);
      color: var(--error-color);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      animation: shake 0.4s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      50% { transform: translateX(5px); }
      75% { transform: translateX(-5px); }
      100% { transform: translateX(0); }
    }

    lottie-player {
      width: 100px;
      height: 100px;
      margin: 0 auto 20px;
    }
  </style>
</head>
<body class="background-overlay">
  <div class="login-card">
    <?php if (!empty($error)): ?>
      <lottie-player src="https://assets4.lottiefiles.com/private_files/lf30_tzxbtrcu.json" background="transparent" speed="1" autoplay></lottie-player>
    <?php else: ?>
      <img src="Logo_STEG.png" alt="Logo Steg">
    <?php endif; ?>

    <h2><?php echo $error ? "Échec de connexion" : "Connexion à StegPoint"; ?></h2>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="input-floating">
        <input type="text" name="username"autocomplete="off" id="username" required placeholder=" ">
        <label for="username">Nom d'utilisateur</label>
      </div>
      <div class="input-floating">
        <input type="password" name="password"autocomplete="off" id="password" required placeholder=" ">
        <label for="password">Mot de passe</label>
      </div>
      <input type="submit" value="Se connecter">
    </form>
  </div>
</body>
</html>
      