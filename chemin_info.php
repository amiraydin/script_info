<?php
// Demandez à l'utilisateur de saisir un chemin
// $customPath = readline("Veuillez saisir un chemin personnalisé : ");
// connexion BD
function connectionBD()
{
    include('config.php');
    try {
        $bdd = new PDO('mysql:host=' . $host . ';dbname=' . $db . ';charset=utf8', '' . $user . '', '' . $password . '');
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Erreur de connexion : ' . $e->getMessage() . " à la ligne " . $e->getLine());
    }
    return $bdd;
}
// fonction message 
function messageAlert($alertMes, $urlNav)
{
    echo '<div style="position: fixed; top: 40%; left: 30%; background-color: #E7F7FF; padding: 13px; border-radius: 10px;">';
    echo '<h3> Message </h3>';
    echo '<span class="separator"></span>';
    echo '<h4 class="text-center" style="position: relative; display: flex; flex-direction: column; width: 100%;">' . $alertMes . '</h4>';
    echo '</div>';
    header("Refresh: 3; url=$urlNav");
    exit();
}
// formater les taille de chaque fichiers 
function formatSizeUnits($bytes)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
// vider l'array resultat !
$result = [];
$extension = [];
if (!empty($_POST['clearArray'])) {
    // Réinitialiser le tableau à vide
    $result = [];
    header("Refresh: 1; url= index.php");
    exit();
    // Envoyer une réponse au client (vous pouvez personnaliser le message)
    // echo 'Array cleared successfully.';
}

// Fonction pour obtenir l'ID d'un chemin (s'il existe) ou l'insérer s'il est nouveau
function getOrInsertPathId($path)
{
    $bdd = connectionBD();
    $stmt = $bdd->prepare('SELECT id FROM chemin WHERE name = :name');
    $stmt->bindParam(':name', $path);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        return $row['id'];
    } else {
        $stmt = $bdd->prepare('INSERT INTO chemin (name) VALUES (:name)');
        $stmt->bindParam(':name', $path);
        $stmt->execute();
        return $bdd->lastInsertId();
    }
}

// Fonction pour obtenir l'ID d'un dossier (s'il existe) ou l'insérer s'il est nouveau
function getOrInsertFolderId($folderName)
{
    $bdd = connectionBD();
    $stmt = $bdd->prepare('SELECT id FROM dossier WHERE name = :name');
    $stmt->bindParam(':name', $folderName);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        return $row['id'];
    } else {
        $stmt = $bdd->prepare('INSERT INTO dossier (name) VALUES (:name)');
        $stmt->bindParam(':name', $folderName);
        $stmt->execute();
        return $bdd->lastInsertId();
    }
}

// Fonction pour obtenir l'ID d'un fichier (s'il existe) ou l'insérer s'il est nouveau
function getOrInsertFileId($fileName, $extension, $size)
{
    $bdd = connectionBD();
    $stmt = $bdd->prepare('SELECT id_fichier FROM fichier WHERE "name" = :name AND extension = :extension AND "size" = :size');
    $stmt->bindParam(':name', $fileName);
    $stmt->bindParam(':extension', $extension);
    $stmt->bindParam(':size', $size);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return $row['id_fichier'];
    } else {
        $stmt = $bdd->prepare('INSERT INTO fichier ("name", extension, "size") VALUES (:name, :extension, :size)');
        $stmt->bindParam(':name', $fileName);
        $stmt->bindParam(':extension', $extension);
        $stmt->bindParam(':size', $size);
        $stmt->execute();
        return $bdd->lastInsertId();
    }
}

// print_r($result);
// recuperer le chemin depuis front avec methode post
if (!empty($_POST['chemin'])) {
    $custumPath = $_POST['chemin'];
    $pathAttendu = "C:\\Users\\Amir\\Desktop\\";
    // echo "custum path :", $custumPath, "\n";
    function getDirectory($path, $result)
    {
        // Utilisez le chemin personnalisé comme répertoire courant
        chdir($path);
        // Obtient le répertoire courant
        $dir = getcwd();
        // echo "path  : => $path <br/>";
        // echo "dir : => $dir <br/>";
        // Obtient la liste des fichiers et répertoires dans le répertoire courant
        $items = scandir($dir);
        // parcourir toutes les fichiers d'abord !
        foreach ($items as $key => $item) {
            if ($item !== '.' && $item !== '..') {
                $path = $dir . '/' . $item;
                // echo "item : =>  $item <br/>";
                // si c'est un fichier
                if (is_file($path)) {
                    $type = "FICHIER";
                    $ext = pathinfo($item, PATHINFO_EXTENSION);
                    $nom = pathinfo($item, PATHINFO_FILENAME);
                    $ext = strtolower($ext);
                    // echo "nom : $nom  ext : $ext<br/>";
                    $size = filesize($path);
                    $formatSize = formatSizeUnits($size);
                    $fileInfo = array(
                        // 'id' => $key - 1,
                        'Type' => "FICHIER",
                        'NomFich' => $nom,
                        'CheminF' => $path,
                        'Ext' => $ext,
                        'Taille' => $formatSize
                    );
                    $result[] = $fileInfo;
                }
                // echo "$type : $item\n";
            }
        }
        // parcourir toutes les dossiers ensuit !
        foreach ($items as $key => $item) {
            if ($item !== '.' && $item !== '..') {
                $path = $dir . '/' . $item;
                // si c'est un dossier
                if (is_dir($path)) {
                    $folderName = basename($path);
                    if (substr($folderName, 0, 1) !== '.') {
                        $fileInfo = array(
                            "NomDoss" => $item,
                            "Type" => "DOSSIER",
                            "Children" => getDirectory($path, [])
                        );
                        $result[] = $fileInfo;
                    }
                }
            }
        }
        return $result;
    }
    function extractExtensions($result)
    {
        $extensions = array();
        foreach ($result as $fileInfo) {
            if (isset($fileInfo['Ext'])) {
                $ext = $fileInfo['Ext'];
                // Vérifier si l'extension n'est pas déjà présente dans le tableau
                if (!in_array($ext, $extensions)) {
                    $extensions[] = $ext;
                }
            }
            // Si le fichier actuel est un dossier et a une clé 'Children'
            if (isset($fileInfo['Type']) && $fileInfo['Type'] === 'DOSSIER' && isset($fileInfo['Children'])) {
                // Appel récursif pour traiter les extensions du contenu du dossier
                $extensions = array_unique(array_merge($extensions, extractExtensions($fileInfo['Children'])));
            }
        }
        return $extensions;
    }
    // Vérifiez si le chemin existe et est un répertoire et qu'il respecte le chemin principal
    if (is_dir($custumPath) && strpos($custumPath, $pathAttendu) === 0) {
        // Appelle la fonction pour explorer récursivement le chemin
        $result = getDirectory($custumPath, $result);
        $extension = extractExtensions($result);
        // envoie une message en cas d'erreur 
    } else {
        messageAlert("Le chemin n'existe pas ou il y a une erreur de syntaxe.</br> Le chemin attendu doit contenir (C:\Users\Amir\Desktop\) </br> Merci !", "index.php");
    }
}
