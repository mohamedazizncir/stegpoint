<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "csv_db 6";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération des champs GET
$firstName = $_GET['first_name'] ?? '';
$lastName = $_GET['last_name'] ?? '';
$department = $_GET['department'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';


// Liste des départements
$departmentsList = [
    "Centrale étape A-B",
    "Centrale étape C",
    "Centrale étape D",
    "Division comptabilité et finance",
    "Division conduite A-B",
    "Division conduite C",
    "Division conduite D",
    "Division gestion contrats de maintenance",
    "Division gestion de Stocks",
    "Division gestion technique et performances A-B",
    "Division gestion technique et performances C",
    "Division gestion technique et performances D",
    "Division informatique",
    "Division maintenance courante A-B",
    "Division maintenance courante C",
    "Division maintenance courante D",
    "Division production, traitement d'eau et chimie A-B",
    "Division production, traitement d'eau et chimie C",
    "Division production, traitement d'eau et chimie D",
    "Division ressource humaines",
    "Division suivi des performances et éfficacité énergétique",
    "Division sécurité et environnement",
    "Département maintenance",
    "GROUPEMENT DE PRODUCTION DE SOUSSE",
    "Service logistique"
];

// Requête dynamique avec filtres
$result = mysqli_query($conn, "SHOW COLUMNS FROM groupement_20250620 LIKE 'heures_supp'");
if (mysqli_num_rows($result) == 0) {
    $sql1 = "ALTER TABLE groupement_20250620 ADD heures_supp VARCHAR(10)";
    mysqli_query($conn, $sql1);
}

$sql2 = " 
UPDATE groupement_20250620
SET heures_supp = 
    CASE 
        WHEN `COL 6` IN ( 'Samedi' , 'Dimanche' ) AND  `COL 9` = '' THEN '-'
        WHEN `COL 6` IN ( 'Samedi' , 'Dimanche' ) AND  `COL 9` != '' THEN `COL 9`
        WHEN `COL 9` = '' AND `COL 7`= ''   THEN 'ABSENT'
        WHEN `COL 9` = '' AND `COL 7` != ''   THEN '-'
         WHEN TIME_TO_SEC(STR_TO_DATE(`COL 9`, '%H:%i')) < 8 * 3600 THEN '00:00'

        ELSE DATE_FORMAT(SEC_TO_TIME(TIME_TO_SEC(STR_TO_DATE(`COL 9`, '%H:%i')) - 8 * 3600  ) , '%H:%i')
    END
  ";
mysqli_query($conn, $sql2);

$sql3 = "
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
        heures_supp
    FROM groupement_20250620
    WHERE `COL 1` REGEXP '^[0-9]+$'
";

if ($firstName !== '') {
    $sql3 .= " AND `COL 2` LIKE '%" . $conn->real_escape_string($firstName) . "%'";
}
if ($lastName !== '') {
    $sql3 .= " AND `COL 3` LIKE '%" . $conn->real_escape_string($lastName) . "%'";
}
if ($department !== '') {
    $sql3 .= " AND `COL 4` = '" . $conn->real_escape_string($department) . "'";
}
if ($startDate !== '' && $endDate !== '') {
    $sql3 .= " AND `COL 5` BETWEEN '" . $conn->real_escape_string($startDate) . "' AND '" . $conn->real_escape_string($endDate) . "'";
} elseif ($startDate !== '') {
    $sql3 .= " AND `COL 5` >= '" . $conn->real_escape_string($startDate) . "'";
} elseif ($endDate !== '') {
    $sql3 .= " AND `COL 5` <= '" . $conn->real_escape_string($endDate) . "'";
}


$result = $conn->query($sql3);
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=export_presence.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Prénom', 'Nom', 'Département', 'Date', 'Jour', 'Entrée', 'Sortie', 'Heures totales']);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['employee_id'],
                $row['first_name'],
                $row['last_name'],
                $row['department'],
                $row['work_date'],
                $row['weekday'],
                $row['check_in'],
                $row['check_out'],
                $row['total_hours'],
                $row['heures_supp']

            ]);
        }
    }

    fclose($output);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Feuille de Présence - STEG</title>
    <link rel="icon" href="Logo_STEG.png" type="image/png" />
<style>
    /* Palette STEG */
    :root {
        --bleu-steg: #005baa;
        --rouge-steg: #d4232a;
        --blanc: #fff;
        --gris-clair: #f5f7fa;
        --gris-moyen: #cccccc;
        --texte-principal: #222;
        --texte-secondaire: #555;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--gris-clair);
        margin: 0;
        padding: 30px 40px 60px;
        color: var(--texte-principal);
          transition: background 0.5s ease, color 0.5s ease;

    }

    header {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 30px;
    }

    header img {
        width: 70px;
        height: auto;
        filter: drop-shadow(0 0 3px rgba(0,0,0,0.1));
    }

    header h1 {
        font-weight: 900;
        font-size: 2.6rem;
        color: var(--bleu-steg);
        text-shadow: 1px 1px 3px rgba(0,91,170,0.4);
        margin: 0;
    }

    form {
        background: var(--blanc);
        padding: 25px 30px;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 20px 30px;
        justify-content: center;
        margin-bottom: 35px;
        max-width: 100%;
    }

    form label {
        display: flex;
        flex-direction: column;
        font-weight: 600;
        font-size: 15px;
        color: var(--texte-secondaire);
        min-width: 210px;
    }

    form input[type="text"],
    form input[type="date"],
    form select {
        margin-top: 8px;
        padding: 11px 14px;
        font-size: 15px;
        border: 2px solid var(--gris-moyen);
        border-radius: 9px;
        transition: border-color 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="date"]:focus,
    form select:focus {
        outline: none;
        border-color: var(--bleu-steg);
        box-shadow: 0 0 10px rgba(0,91,170,0.4);
    }

    form button {
        font-weight: 700;
        font-size: 16px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        padding: 12px 28px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    form button[type="submit"] {
        background-color: var(--rouge-steg);
        color: var(--blanc);
    }
    form button[type="submit"]:hover {
        background-color: #b31b24;
        box-shadow: 0 8px 20px rgba(179,27,36,0.7);
    }

    form button[type="button"] {
        background-color: var(--bleu-steg);
        color: var(--blanc);
        margin-left: 12px;
    }
    form button[type="button"]:hover {
        background-color: #00478c;
        box-shadow: 0 8px 20px rgba(0,71,140,0.7);
    }

    select#department {
        max-height: 140px;
        overflow-y: auto;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        background: var(--blanc);
        box-shadow: 0 9px 25px rgba(0,0,0,0.1);
        border-radius: 16px;
        overflow: hidden;
        font-size: 15px;
    }

    th, td {
        padding: 14px 20px;
        text-align: center;
        color: var(--texte-secondaire);
    }

    th {
        background-color: var(--bleu-steg);
        color: var(--blanc);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        user-select: none;
    }

    tbody tr {
        background: #fefefe;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
    }

    tbody tr:hover {
        background-color: #fff0f0;
        box-shadow: 0 5px 18px rgba(212,35,42,0.3);
        cursor: pointer;
    }

    tbody tr td:first-child {
        font-weight: 700;
        color: var(--rouge-steg);
    }

    /* Responsive */
    @media (max-width: 980px) {
        form {
            flex-direction: column;
            align-items: stretch;
        }
        form label {
            min-width: auto;
            width: 100%;
        }
        form button {
            width: 100%;
            margin-left: 0;
            margin-top: 14px;
        }
        table, th, td {
            font-size: 13px;
        }
    }
    .row-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 18px 30px; 
    justify-content: center;
    margin-bottom: 20px;
}

.row-filters > label {
    flex: 1 1 220px;
    min-width: 180px;
    display: flex;
    flex-direction: column; /* Important : label au dessus de input */
}


.buttons-group {
    display: flex;
    justify-content: center;
    gap: 14px;
    margin-top: 10px;
}
form button.rapport-mensuel {
    background-color: ##0074cc;
}
form button.rapport-mensuel:hover {
    background-color: #005baa;
    box-shadow: 0 8px 20px rgba(0, 91, 170, 0.5);
}
form label {
    position: relative; /* pour que la boîte reste dans le champ */
}
form button.rapport-mensuel {
    background-color: #0074cc;
}
form button.export-csv {
    background-color: #1d4ed8; /* Bleu clair moderne */
    color: var(--blanc);
    border: 2px solid #005baa;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

form button.export-csv:hover {
    background-color: #0a2e8a; /* Bleu foncé STEG */
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 91, 170, 0.5);
}


</style>
</head>
<body>
    <header>
    <img src="Logo_STEG.png" alt="Logo STEG" />
    <h1>Feuille de Présence</h1>

    </header>


    <form method="get" action="">
    <div class="row-filters">
        <label>Prénom :
            <input type="text" name="first_name" autocomplete="off" value="<?= htmlspecialchars($firstName) ?>">
        </label>
        <label>Nom :
            <input type="text" name="last_name" autocomplete="off" value="<?= htmlspecialchars($lastName) ?>">
        </label>

        <label>Département :
            <select id="department" name="department">
                <option value="" <?= ($department === '') ? 'selected' : '' ?>>-- Tous les départements --</option>
                    <?php foreach ($departmentsList as $dep): ?>
                <option value="<?= htmlspecialchars($dep) ?>" <?= ($dep === $department) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dep) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </label>

        <label>Date de début :
            <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
        </label>

        <label>Date de fin :
            <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
        </label>
         <div id="date-error" style="color: red; margin-top: 5px; display: none;"></div>
    </div>

    <div class="buttons-group">
        <button type="submit">Rechercher</button>
        <a href="attendance.php" style="text-decoration:none;">
            <button type="button">Réinitialiser</button>
        </a>
        <a href="rapport_mensuel.php" style="text-decoration: none;">
            <button type="button"class="rapport-mensuel">Rapport</button>
        </a>
        <button type="submit" name="export" value="csv" class="export-csv">Exporter CSV</button>

    </div>
</form>



    <table>
        <tr>
            <th>Numéro</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Département</th>
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
                    <td>
                        <a href="employee_details.php?id=<?= urlencode($row['employee_id']) ?>&month=<?= urlencode(substr($row['work_date'], 0, 7)) ?>" style="color: var(--rouge-steg); text-decoration: none; font-weight: bold;">
                        <?= htmlspecialchars($row['employee_id']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><?= htmlspecialchars(date("d/m/Y", strtotime($row['work_date']))) ?></td>
                    <td><?= htmlspecialchars($row['weekday']) ?></td>
                    <td><?= htmlspecialchars($row['check_in']) ?></td>
                    <td><?= htmlspecialchars($row['check_out']) ?></td>
                    <td><?= htmlspecialchars($row['total_hours']) ?></td>
                    <td><?= htmlspecialchars($row['heures_supp']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10">Aucune donnée trouvée</td></tr>
        <?php endif; ?>
    </table>
<script>
function setupAutocomplete(inputName, type) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    const wrapper = document.createElement('div');
    wrapper.style.position = 'relative';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(wrapper);

    const suggestionBox = document.createElement('div');
    suggestionBox.style.position = 'absolute';
    suggestionBox.style.top = input.offsetHeight + 'px';
    suggestionBox.style.left = 0;
    suggestionBox.style.right = 0;
    suggestionBox.style.backgroundColor = '#fff';
    suggestionBox.style.border = '1px solid #ccc';
    suggestionBox.style.zIndex = 1000;
    suggestionBox.style.maxHeight = '160px';
    suggestionBox.style.overflowY = 'auto';
    suggestionBox.style.display = 'none';
    wrapper.appendChild(suggestionBox);

    input.addEventListener('input', function () {
        const query = input.value;
        if (query.length < 2) {
            suggestionBox.style.display = 'none';
            return;
        }

        fetch(`autocomplete.php?type=${type}&query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                suggestionBox.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(name => {
                        const div = document.createElement('div');
                        div.textContent = name;
                        div.style.padding = '8px 12px';
                        div.style.cursor = 'pointer';
                        div.addEventListener('click', () => {
                            input.value = name;
                            suggestionBox.style.display = 'none';
                        });
                        suggestionBox.appendChild(div);
                    });
                    suggestionBox.style.display = 'block';
                } else {
                    suggestionBox.style.display = 'none';
                }
            });
    });

    document.addEventListener('click', (e) => {
        if (!suggestionBox.contains(e.target) && e.target !== input) {
            suggestionBox.style.display = 'none';
        }
    });
}

setupAutocomplete('first_name', 'first_name');
setupAutocomplete('last_name', 'last_name');
</script>
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
</body>
</html>

<?php $conn->close(); ?>
