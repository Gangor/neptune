<?php

require CORE. "/controller.php";
require CORE. "/email.php";

require MODELS. "/clients/createModel.php";
require MODELS. "/clients/editModel.php";
require MODELS. "/shared/searchModel.php";

class clientsController extends Controller
{
    private $users;

    public function __construct()
    {
        $this->email = new Email();
        $this->users = new Users();
    }

    /*
     *
     * GET : /clients/index
     * Page de gestion des clients
     * 
     */
    public function index()
    {
        if ( !$this->user || !$this->user->admin )
            $this->unauthorized();

        $model = new SearchModel( true );

        $this->view[ 'users' ] = $this->users->GetUsers( $model->Search ?? '' );
        $this->render( 'index', $model );
    }

    /*
     *
     * GET : /clients/create
     * Page d'inscription
     * 
     */
    public function create()
    {
        $model = new createModel( true );
        
        $this->view[ 'pays' ] = $this->users->GetPays();
        $this->render( 'create', $model );
    }

    /*
     *
     * POST : /clients/createconfirm
     * Page de confirmation d'inscription
     * 
     */
    public function createconfirm()
    {
        $model = new createModel();
        
        if ( $model->IsValid )
        {
            // Exist user ?
            if ( !$this->users->GetUserByEmail( $model->Email ) )
            {
                $newUser = new stdClass();

                $newUser->civilite      = $model->Civilite;
                $newUser->nom           = $model->Nom;
                $newUser->prenom        = $model->Prenom;

                if ( !$model->Admin )
                {
                    $newUser->codePostal    = $model->CodePostal;
                    $newUser->adresse       = $model->Adresse;
                    $newUser->ville         = $model->Ville;
                    $newUser->pays_id       = (int)$model->Pays;
                }

                $newUser->identifiant   = $model->Email;
                $newUser->motdepasse    = sha1( $model->Password );
                $newUser->cle           = uniqid();
                $newUser->confirme      = (int)$model->Confirme;
                $newUser->admin         = (int)$model->Admin;
                
                if ( $this->users->Create( $newUser ) )
                {
                    if ( !$model->confirme )
                        $this->email->Send( $newUser, 'Inscription', 'register' );

                    Router::redirectLocal( 'clients', 'index' );
                }
                else $this->view[ 'error' ] = 'Une erreur ses produite lors de la création du compte.';
            }
            else $this->view[ 'error' ] = 'Cette adresse mail est déjà utilisé.';
        }
        else $this->view[ 'error' ] = 'Un ou plusieurs champs ne sont pas correctement remplis.';

        $this->view[ 'pays' ] = $this->users->GetPays();
        $this->render( 'create', $model );
    }

    /**
     * 
     * GET : /clients/delete/{id}
     * Suppression d'une chambre
     * 
     * @param   int $id ID de la chambre
     * 
     */
    public function delete( int $id )
    {
        if ( !$this->user || !$this->user->admin )
            $this->unauthorized();

        $user = $this->users->GetUserById( $id );

        if ( $user == null )
            $this->not_found();

        $this->render( 'delete', $user );
    }

    /**
     * 
     * POST : /clients/deleteConfirm/{id}
     * Confirmation de suppression d'une chambre
     * 
     * @param   int $id ID de l'utilisateur
     * 
     */
    public function deleteConfirm( int $id )
    {
        if ( !$this->user || !$this->user->admin )
            $this->unauthorized();

        $user = $this->users->GetUserById( $id );

        if ( $user == null )
            $this->not_found();

        if ( $this->users->delete( $user->id ) )
        {
            Router::redirectLocal( 'clients', 'index' );
        }
        else $this->view["error"] = "Une erreur ses produite lors de la suppresion de l'utilisateur.";

        $this->render( 'delete', $user );
    }

    /*
     *
     * GET : /clients/edit
     * Page des paramètres utilisateur
     * 
     * @param   int $id ID de l'utilisateur
     * 
     */
    public function edit( int $id )
    {
        if ( !$this->user || !$this->user->admin )
            $this->unauthorized();

        $user = $this->users->GetUserById( $id );
        
        if ( $user == null )
            $this->not_found();

        $model = new EditModel( true );
        $model->Parse( $user );

        $this->view[ 'edituser' ] = $user;
        $this->view[ 'pays' ] = $this->users->GetPays();
        $this->render( 'edit', $model );
    }

    /*
     *
     * POST : /clients/editconfirm
     * Page de confirmation des paramètres utilisateur
     * 
     * @param   int $id ID de l'utilisateur
     * 
     */
    public function editconfirm( int $id )
    {
        if ( !$this->user || !$this->user->admin )
            $this->unauthorized();

        $model = new EditModel();
        $user = $this->users->GetUserById( $id );
        
        if ( $user == null )
            $this->not_found();

        if ( $model->IsValid )
        {
            $user->civilite     = $model->Civilite;
            $user->prenom       = $model->Prenom;
            $user->nom          = $model->Nom;
            $user->pays_id      = (int)$model->Pays;
            $user->adresse      = $model->Adresse;
            $user->ville        = $model->Ville;
            $user->codePostal   = $model->CodePostal;

            if ( $this->users->Update( $user ) )
            {
                Router::redirectLocal( 'clients', 'index' );

            }
            else $this->view[ 'error' ] = 'Une erreur ses produite lors de l\'édition du compte.';
        }
        else $this->view[ 'error' ] = 'Un ou plusieurs champs ne sont pas correctement remplis.';

        $this->view[ 'pays' ] = $this->users->GetPays();
        $this->render( 'edit', $model );
    }
}

?>