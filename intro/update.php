<?php

// Le fichier boot.php contient la configuration et la connection à la base de données
require __DIR__ . '/boot.php';

use App\Manager\UserManager;
use App\Repository\UserRepository;

// Créer une instance de la classe \App\Repository\UserRepository
$repository = new UserRepository($connection);

// Utiliser cette instance pour récupérer l'utilisateur à afficher.
// L'identifiant de l'utilisateur à afficher peut être récupéré dans la variable globale $_GET['id']
// Contrôler la valeur de l'identifiant (nombre entier supérieur à zéro).
// Si l'identifiant n'est pas valide (c'est sans doute qu'un lien est mal formatté dans un autre fichier PHP),
// rediriger l'internaute ou afficher un message d'erreur.
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (0 >= $id) {
    http_response_code(302);
    header('Location: list.php');
    exit;
}

$user = $repository->findOneById($id);

// Contrôler si le formulaire à été soumis, en vérfiant la valeur du champ de type 'hidden'.
// On vérifie donc si l'index 'user' existe dans le tableau $_POST (données soumises
// par le formulaire) et si la valeur associée à cet index est égale à 'user'
if (array_key_exists('user', $_POST) && $_POST['user'] === 'user') {
    // (Si le formulaire a été soumis)
    // Utiliser les mutateurs (méthodes set* de la classe \App\Entity\User)
    // pour mettre à jour l'utilisateur avec les données du formulaire
    // Exemple: $user->setEmail($_POST['email']);
    $user->setEmail($_POST['email']);
    $user->setName($_POST['name']);
    if (isset($_POST['birthday']) && !empty($_POST['birthday'])) {
        $user->setBirthday(new \DateTime($_POST['birthday']));
    } else {
        $user->setBirthday(null);
    }
    if (isset($_POST['active']) && $_POST['active'] == 1) {
        $user->setActive(true);
    } else {
        $user->setActive(false);
    }

    // Créer une instance de la classe \App\Manager\UserManager
    $manager = new UserManager($connection);

    // Utiliser ce 'manager' pour mettre à jour l'utilisateur dans la base de données
    $manager->persist($user);

    // Si la mise à jour dans la base de donnée a réussi,
    // rediriger vers le détail de l'utilisateur (read.php?id= ???)
    // (voir la page Astuces (Tips) de la documentation pour un exemple de redirection)
    http_response_code(302);
    header('Location: read.php?id=' . $user->getId());
    exit;
}

// Plus bas dans le code HTML, utiliser les accesseurs (méthodes get* de la classe \App\Entity\User)
// pour afficher les valeurs des différentes propriétés de l'utilisateur
// (à insérer dans les attributs value="" des balises <input>)

?>
<!doctype html>
<html lang="fr">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Administration</title>
</head>
<body>

<?php include 'includes/topbar.php'; ?>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Modifier l'utilisateur</h1>
                <!-- Masquer ce calque (div) si l'utilisateur est introuvable -->
                <?php if ($user) { ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <!-- Lien vers la page "Liste des utiliateurs" -->
                    <a href="list.php" class="btn btn-default">
                        Retour
                    </a>
                    &nbsp;
                    <!-- Lien vers la page "Supprimer l'utiliateur" -->
                    <!-- Ajouter l'identifiant de l'utilisateur dans l'attribut 'href' du lien -->
                    <a href="delete.php?id=" class="btn btn-danger">
                        Supprimer
                    </a>
                </div>
                <?php } ?>
            </div>

            <!-- Si l'utilisateur est introuvable, afficher le message d'erreur si dessous -->
            <?php if (!$user) { ?>
            <div class="alert alert-danger">
                Utiliateur introuvable
            </div>

            <?php } else { ?>
            <!-- Sinon, afficher le formulaire de mise à jour de l'utilisateur -->
            <!-- Ajouter l'identifiant de l'utilisateur dans l'attribut 'action' du formulaire -->
            <form action="update.php?id=<?php echo $user->getId(); ?>" method="post">
                <!-- Champ masqué pour déterminer si le formulaire a été soumis -->
                <input type="hidden" name="user" value="user">
                <!-- Champ "Email" -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Email"
                           value="<?php echo $user->getEmail(); ?>" required="required">
                </div>
                <!-- Champ "Nom" -->
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Nom"
                           value="<?php echo $user->getName(); ?>" required="required">
                </div>
                <!-- Champ "Date de naissance" -->
                <div class="form-group">
                    <label for="birthday">Date de naissance</label>
                    <input type="date" class="form-control" id="birthday" name="birthday" placeholder="Date de naissance"
                           value="<?php echo $user->getBirthday() ? $user->getBirthday()->format('Y-m-d') : ''; ?>">
                </div>
                <!-- Champ "Actif" -->
                <div class="form-group form-check">
                    <!-- Ajouter l'attribut « checked="checked" » pour ne pas être cochée par défaut -->
                    <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
                        <?php echo $user->isActive() ? 'checked="checked' : ''; ?>>
                    <label for="active">Actif</label>
                </div>
                <!-- Boutton de soumission -->
                <button type="submit" class="btn btn-primary">Modifier</button>
            </form>
            <?php } ?>

        </main>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>

