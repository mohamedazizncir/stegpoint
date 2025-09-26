<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "csv_db 6";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employeeId = $_GET['id'] ?? '';
$month = $_GET['month'] ?? ''; // Format : YYYY-MM

// Récupérer les dates de début et fin, si définies
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Vérifier les paramètres
if (!$employeeId) {
    die("Paramètre ID manquant.");
}

$dateCondition = "";
if ($startDate !== '' && $endDate !== '') {
    // Filtre entre dates
    $startDateSafe = $conn->real_escape_string($startDate);
    $endDateSafe = $conn->real_escape_string($endDate);
    $dateCondition = "AND `COL 5` BETWEEN '$startDateSafe' AND '$endDateSafe'";
} elseif ($month !== '') {
    // Filtre par mois
    $monthSafe = $conn->real_escape_string($month);
    $dateCondition = "AND DATE_FORMAT(`COL 5`, '%Y-%m') = '$monthSafe'";
} else {
    die("Paramètres de date manquants.");
}

// Requête principale
$sql = "
    SELECT 
        `COL 1` AS employee_id,
        `COL 2` AS first_name,
        `COL 3` AS last_name,
        `COL 4` AS department,
        `COL 5` AS work_date,
        `COL 6` AS weekday,
        `COL 7` AS check_in,
        `COL 8` AS check_out,
        `COL 9` AS total_hours,
        `heures_supp`
    FROM groupement_20250620
    WHERE `COL 1` = '" . $conn->real_escape_string($employeeId) . "'
      $dateCondition
    ORDER BY `COL 5` ASC
";

$result = $conn->query($sql);

$employeeInfo = null;
if ($result && $result->num_rows > 0) {
    $employeeInfo = $result->fetch_assoc();
    $result->data_seek(0);
}

// Calculer total des heures sup dans la période
$sqlExtra = "
    SELECT 
        DATE_FORMAT(
            SEC_TO_TIME(
                SUM(
                    CASE 
                        WHEN `heures_supp` NOT IN ('-', 'ABSENT') AND `heures_supp` IS NOT NULL
                        THEN TIME_TO_SEC(STR_TO_DATE(`heures_supp`, '%H:%i'))
                        ELSE 0
                    END
                )
            ),
            '%H:%i'
        ) AS total_extra_hours
    FROM groupement_20250620
    WHERE `COL 1` = '" . $conn->real_escape_string($employeeId) . "'
      $dateCondition
";
$resExtra = $conn->query($sqlExtra);
$totalExtra = ($resExtra && $resExtra->num_rows > 0) ? $resExtra->fetch_assoc()['total_extra_hours'] : '00:00';
$totalAbsences = 0;

// Si période personnalisée
if ($startDate !== '' && $endDate !== '') {
    $sqlAbsence = "
        SELECT COUNT(*) AS total_absences
        FROM (
            SELECT d.date
            FROM (
                SELECT DATE_ADD('$startDate', INTERVAL a.DAY DAY) AS date
                FROM (
                    SELECT @row := @row + 1 AS DAY
                    FROM (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
                         (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
                         (SELECT @row := -1) r
                ) a
                WHERE DATE_ADD('$startDate', INTERVAL a.DAY DAY) <= '$endDate'
            ) d
            LEFT JOIN groupement_20250620 g 
              ON g.`COL 1` = '" . $conn->real_escape_string($employeeId) . "' 
             AND g.`COL 5` = d.date
            WHERE DAYOFWEEK(d.date) NOT IN (1, 7) -- Exclut dim (1) et sam (7)
              AND (g.`COL 7` IS NULL OR g.`COL 7` = '')
              AND (g.`COL 8` IS NULL OR g.`COL 8` = '')
        ) AS absents
    ";
    $resAbs = $conn->query($sqlAbsence);
    if ($resAbs && $resAbs->num_rows > 0) {
        $totalAbsences = $resAbs->fetch_assoc()['total_absences'];
    }
}

// Si filtré par mois
elseif ($month !== '') {
    $sqlDates = "
        SELECT MIN(`COL 5`) AS min_date, MAX(`COL 5`) AS max_date
        FROM groupement_20250620
        WHERE `COL 1` = '" . $conn->real_escape_string($employeeId) . "' 
          AND DATE_FORMAT(`COL 5`, '%Y-%m') = '" . $conn->real_escape_string($month) . "'
    ";
    $resDates = $conn->query($sqlDates);
    if ($resDates && $row = $resDates->fetch_assoc()) {
        $startDate = $row['min_date'];
        $endDate = $row['max_date'];

        if ($startDate && $endDate) {
            $sqlAbsence = "
                SELECT COUNT(*) AS total_absences
                FROM (
                    SELECT d.date
                    FROM (
                        SELECT DATE_ADD('$startDate', INTERVAL a.DAY DAY) AS date
                        FROM (
                            SELECT @row := @row + 1 AS DAY
                            FROM (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
                                 (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
                                 (SELECT @row := -1) r
                        ) a
                        WHERE DATE_ADD('$startDate', INTERVAL a.DAY DAY) <= '$endDate'
                    ) d
                    LEFT JOIN groupement_20250620 g 
                      ON g.`COL 1` = '" . $conn->real_escape_string($employeeId) . "' 
                     AND g.`COL 5` = d.date
                    WHERE DAYOFWEEK(d.date) NOT IN (1, 7)
                      AND (g.`COL 7` IS NULL OR g.`COL 7` = '')
                      AND (g.`COL 8` IS NULL OR g.`COL 8` = '')
                ) AS absents
            ";
            $resAbs = $conn->query($sqlAbsence);
            if ($resAbs && $resAbs->num_rows > 0) {
                $totalAbsences = $resAbs->fetch_assoc()['total_absences'];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Détails Employé - STEG</title>
    <link rel="icon" href="Logo_STEG.png" type="image/png" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* Ton CSS existant */
        :root {
            --bleu-steg: #005baa;
            --rouge-steg: #d4232a;
            --blanc: #fff;
            --gris-clair: #f5f7fa;
            --texte: #333;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--gris-clair);
            margin: 0;
            padding: 40px;
            color: var(--texte);
        }
        header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        header img {
            width: 60px;
        }
        header h1 {
            font-size: 2rem;
            color: var(--bleu-steg);
        }
        .info-box {
            background: var(--blanc);
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .info-box strong {
            color: var(--bleu-steg);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--blanc);
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 16px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background: var(--bleu-steg);
            color: var(--blanc);
            text-transform: uppercase;
            font-size: 14px;
        }
        tr:hover {
            background: #eef5ff;
        }
        .btn-retour {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 22px;
            background-color: var(--bleu-steg);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .btn-retour:hover {
            background-color: #003e7e;
        }
        .scroll-wrapper {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            table {
                font-size: 13px;
            }
        }
        /* Form filtre date */
        form.filter-form {
            margin-bottom: 25px;
        }
        form.filter-form label {
            margin-right: 20px;
            font-weight: 600;
        }
        form.filter-form input[type="date"] {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-left: 6px;
        }
        form.filter-form button {
            padding: 7px 20px;
            background-color: var(--rouge-steg);
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }
        form.filter-form button:hover {
            background-color: #a91922;
        }
        .buttons-top-right {
    display: flex;
    justify-content: flex-end;
    gap: 10px; /* espace entre boutons */
    margin-bottom: 20px;
}
.btn-pdf {
    background-color: var(--rouge-steg);
    color: white;
    font-weight: bold;
    border: none;
    cursor: pointer;
}

.btn-pdf:hover {
    background-color: #a91922;
}
@media print {
    .no-print {
        display: none !important;
    }
}
table {
    table-layout: auto; /* Permet une largeur dynamique selon contenu */
    width: 100%;
}

th, td {
    padding: 12px 10px; /* Réduit un peu le padding pour plus de place */
    white-space: nowrap; /* Empêche le texte de passer à la ligne */
    overflow: hidden;
    text-overflow: ellipsis; /* Coupe le texte trop long par ... */
}

th:nth-child(6), td:nth-child(6) {
    min-width: 110px; /* Assure une largeur minimale pour la colonne heures supp */
    max-width: 140px; /* Limite max pour éviter d’être trop large */
    white-space: nowrap; /* Pas de retour à la ligne */
}
th {
  white-space: normal;       /* Permet le retour à la ligne dans les entêtes */
  word-wrap: break-word;     /* Coupe les mots longs */
  padding: 14px 10px;
  font-size: 14px;
  text-transform: uppercase;
  color: var(--blanc);
  background: var(--bleu-steg);
}

/* Optionnel : limiter la largeur globale des colonnes */
th:nth-child(6), td:nth-child(6) {
  min-width: 140px;  /* Plus large pour “Heures Supplémentaires” */
  max-width: 180px;
  white-space: normal;  /* Permet au texte de s’étendre sur 2 lignes si besoin */
  word-wrap: break-word;
}



    </style>
</head>
<body>

<header>
    <img src="Logo_STEG.png" alt="Logo STEG">
    <h1>Rapport Journalier - Détails Employé</h1>

    
</header>


<!-- Formulaire sélection dates -->
<!-- Formulaire sélection dates -->
<form method="get" action="" class="filter-form no-print">
    <input type="hidden" name="id" value="<?= htmlspecialchars($employeeId) ?>">
    <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">

    <label for="start_date">Date début:
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
    </label>

    <label for="end_date">Date fin:
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
    </label>
    <div id="date-error" style="color: red; margin-top: 5px; display: none;"></div>

    <button type="submit">Filtrer</button>
</form>

<div class="buttons-top-right no-print">
    <a href="attendance.php" class="btn-retour">← Feuille de Présence</a>
    <a href="rapport_mensuel.php" class="btn-retour">Rapport →</a>
    <button id="exportPdf" class="btn-retour btn-pdf">📄 Exporter PDF</button>
</div>


<div class="info-box">
    <?php if ($employeeInfo): ?>
        <p><strong>ID :</strong> <?= htmlspecialchars($employeeInfo['employee_id']) ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($employeeInfo['last_name']) ?> <?= htmlspecialchars($employeeInfo['first_name']) ?></p>
        <p><strong>Département :</strong> <?= htmlspecialchars($employeeInfo['department']) ?></p>
        <?php if ($startDate && $endDate): ?>
    <p><strong>Période :</strong> <?= htmlspecialchars($startDate) . ' → ' . htmlspecialchars($endDate) ?></p>

<?php else: ?>
    <p><strong>Période :</strong></p>
<?php endif; ?>


        <p><strong>Total Heures Supplémentaires :</strong> <?= htmlspecialchars($totalExtra) ?></p>
        <p><strong>Total Absences :</strong> <?= htmlspecialchars($totalAbsences) ?> jour(s)</p>

    <?php else: ?>
        <p>Aucune information trouvée.</p>
    <?php endif; ?>
</div>

<div class="scroll-wrapper">
    <table>
        <tr>
            <th>Date</th>
            <th>Jour</th>
            <th>Entrée</th>
            <th>Sortie</th>
            <th>Heures Totales</th>
            <th>Heures Supplémentaires</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['work_date']) ?></td>
                    <td><?= htmlspecialchars($row['weekday']) ?></td>
                    <td><?= htmlspecialchars($row['check_in']) ?></td>
                    <td><?= htmlspecialchars($row['check_out']) ?></td>
                    <td><?= htmlspecialchars($row['total_hours']) ?></td>
                    <td><?= htmlspecialchars($row['heures_supp']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Aucune donnée disponible</td></tr>
        <?php endif; ?>
    </table>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(event) {
    const startDate = this.querySelector('input[name="start_date"]').value;
    const endDate = this.querySelector('input[name="end_date"]').value;
    const errorDiv = document.getElementById('date-error');
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';

    if (startDate && endDate && startDate > endDate) {
        event.preventDefault();
        errorDiv.textContent = "Erreur : la date de début doit être inférieure ou égale à la date de fin.";
        errorDiv.style.display = 'block';
    }
});
</script>
<script>
document.getElementById('exportPdf').addEventListener('click', () => {
    // Cacher les éléments "no-print"
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');

    const element = document.body;

    // Nom dynamique
    const id = "<?= htmlspecialchars($employeeInfo['employee_id'] ?? 'employe') ?>";
    const nom = "<?= htmlspecialchars($employeeInfo['last_name'] ?? '') ?>";
    const prenom = "<?= htmlspecialchars($employeeInfo['first_name'] ?? '') ?>";
    const nomFichier = `fiche_${id}_${nom}_${prenom}`.replace(/\s+/g, '_') + '.pdf';

    const opt = {
        margin: 0.5,
        filename: nomFichier,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        // Réafficher les éléments après export
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>
