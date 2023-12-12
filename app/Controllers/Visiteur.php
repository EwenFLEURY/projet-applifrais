<?php namespace App\Controllers;

use CodeIgniter\Controller;
use \App\Models\Authentif;
use \App\Models\ActionsVisiteur;

/**
 * Contrôleur du module VISITEUR de l'application
*/
class Visiteur extends BaseController {

   private $authentif;
   private $actVisiteur;
   private $idVisiteur;
   
   private function checkAuth()
   {
		// contrôle de la bonne authentification de l'utilisateur
		// TODO : 	Lorsque des comptables utiliseront cette même application, il faudra enrichir 
		//			ce code. En effet les comptables n'ont pas le droit d'accéder à ce code et, par
		//			ailleurs, les visiteurs n'auront pas d'accès au controleur des comptables !!!
		$this->authentif = new Authentif();
		if (!$this->authentif->estConnecte()) 
		{
			$res = false;
		}
		else 
		{
			$this->actVisiteur = new ActionsVisiteur();

			$this->session = session();
			$this->idVisiteur = $this->session->get('idUser');
			$this->actVisiteur->checkLastSix($this->idVisiteur);
			$res = true;
		}
		return $res;
	}

   private function unauthorizedAccess()
   {
		// l'accès à ce contrôleur n'est pas autorisé : on renvoie une vue erreur
		return view ('errors/html/error_401');
		// on aurait aussi  pu renvoyer vers le contrôleur par défaut comme suit : 
		//return redirect()->to('/applifrais-CI4/public/anonyme');
   }

	public function index()
	{
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		// envoie de la vue accueil du visiteur
		$data['identite'] = $this->session->get('prenom').' '.$this->session->get('nom');

		return view('v_visiteurAccueil', $data);
	}

	public function  mesFiches($message = "")
	{
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		$data['identite'] = $this->session->get('prenom').' '.$this->session->get('nom');
		$data['mesFiches'] = $this->actVisiteur->getLesFichesDuVisiteur($this->idVisiteur);
		$data['notify'] = $message;

		return view('v_visiteurMesFiches', $data);	
	}

	public function seDeconnecter()	
	{
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		return $this->authentif->deconnecter();
	}

	public function voirMaFiche($mois)
	{	// TODO : contrôler la validité du paramètre (mois de la fiche à consulter)
	
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		$data['identite'] = $this->session->get('prenom').' '.$this->session->get('nom');
		$data['mois'] = $mois;
		$data['fiche'] = $this->actVisiteur->getUneFiche($this->idVisiteur, $mois);
		
		return view('v_visiteurVoirFiche', $data);
	}

	public function modMaFiche($mois, $message = "")
	{	// TODO : contrôler la validité du second paramètre (mois de la fiche à modifier)
	
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		$data['identite'] = $this->session->get('prenom').' '.$this->session->get('nom');
		$data['notify'] = $message;
		$data['mois'] = $mois;
		$data['fiche'] = $this->actVisiteur->getUneFiche($this->idVisiteur, $mois);
		
		return view('v_visiteurModFiche', $data);
	}

	public function signeMaFiche($mois)
	{	// TODO : contrôler la validité du second paramètre (mois de la fiche à modifier)

		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		$this->actVisiteur->signeFiche($this->idVisiteur, $mois);

		// ... et on revient à mesFiches
		return $this->mesFiches("La fiche $mois a été signée. <br/>Pensez à envoyer vos justificatifs afin qu'elle soit traitée par le service comptable rapidement.");
	}

	public function majForfait($mois)
	{	// TODO : conrôler que l'obtention des données postées ne rend pas d'erreurs
		// TODO : dans la dynamique de l'application, contrôler que l'on vient bien de modFiche
		
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		// obtention des données postées
		$lesFrais = $this->request->getPost('lesFrais');

		$this->actVisiteur->majForfait($this->idVisiteur, $mois, $lesFrais);

		// ... et on revient en modification de la fiche
		return $this->modMaFiche($mois, 'Modification(s) des éléments forfaitisés enregistrée(s) ...');
	}
	
	public function ajouteUneLigneDeFrais($mois)
	{	// TODO : conrôler que l'obtention des données postées ne rend pas d'erreurs
		// TODO : dans la dynamique de l'application, contrôler que l'on vient bien de modFiche
		
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		// obtention des données postées
		$uneLigne = array( 
			'dateFrais' => $this->request->getPost('dateFrais'),
			'libelle' => $this->request->getPost('libelle'),
			'montant' => $this->request->getPost('montant')
		);
		$this->actVisiteur->ajouteFrais($this->idVisiteur, $mois, $uneLigne);

		// ... et on revient en modification de la fiche
		return $this->modMaFiche($mois, 'Ligne "Hors forfait" ajoutée ...');				
	}
	
	public function supprUneLigneDeFrais($mois, $idLigneFrais)
	{	// TODO : contrôler la validité du second paramètre (mois de la fiche à modifier)
		// TODO : dans la dynamique de l'application, contrôler que l'on vient bien de modFiche
	
		if (!$this->checkAuth()) return $this->unauthorizedAccess();
		// l'id de la ligne à supprimer doit avoir été transmis en second paramètre
		$this->actVisiteur->supprFrais($this->idVisiteur, $mois, $idLigneFrais);

		// ... et on revient en modification de la fiche
		return $this->modMaFiche($mois, 'Ligne "Hors forfait" supprimée ...');				
	}
}