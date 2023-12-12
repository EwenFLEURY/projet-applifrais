<?php namespace App\Models;

use CodeIgniter\Model;
use \App\Models\DataAccess;
use \App\Models\Tools;

class ActionsVisiteur extends Model {

	private $dao;
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

		// chargement du modèle d'accès aux données qui est utile à toutes les méthodes
		$this->dao = new DataAccess();
    }

	/**
	 * Mécanisme de contrôle d'existence des fiches de frais sur les 6 derniers 
	 * mois pour un visiteur donné. 
	 * Si l'une d'elle est absente, elle est créée.
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	*/
	public function checkLastSix($idVisiteur)
	{	
		// obtention de la liste des 6 derniers mois (y compris celui ci)
		$lesMois = Tools::getSixDerniersMois();

		// contrôle de l'existence des 6 dernières fiches et création si nécessaire
		foreach ($lesMois as $unMois){
			if(!$this->dao->existeFiche($idVisiteur, $unMois)) $this->dao->creeFiche($idVisiteur, $unMois);
		}
	}
	
	/**
	 * Liste les fiches existantes d'un visiteur 
	 *
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $message : message facultatif destiné à notifier l'utilisateur du résultat d'une action précédemment exécutée
	*/
	public function getLesFichesDuVisiteur($idVisiteur, $message=null)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
	
		return $this->dao->getFiches($idVisiteur);
	}	

	/**
	 * Retourne le détail de la fiche sélectionnée 
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $mois : le mois de la fiche à modifier 
	*/
	public function getUneFiche($idVisiteur, $mois)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session

		$res = array();
		
		$res['lesFraisHorsForfait'] = $this->dao->getLesLignesHorsForfait($idVisiteur,$mois);
		$res['lesFraisForfait'] = $this->dao->getLesLignesForfait($idVisiteur,$mois);		
		
		return $res;
	}

	/**
	 * Signe une fiche de frais en changeant son état
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $mois : le mois de la fiche à signer
	*/
	public function signeFiche($idVisiteur, $mois)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : intégrer une fonctionnalité d'impression PDF de la fiche

	    $this->dao->signeFiche($idVisiteur, $mois);
	}

	/**
	 * Modifie les quantités associées aux frais forfaitisés dans une fiche donnée
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $mois : le mois de la fiche concernée
	 * @param $lesFrais : les quantités liées à chaque type de frais, sous la forme d'un tableau
	*/
	public function majForfait($idVisiteur, $mois, $lesFrais)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : valider les données contenues dans $lesFrais ...
		
		$this->dao->majLignesForfait($idVisiteur,$mois,$lesFrais);
		$this->dao->recalculeMontantFiche($idVisiteur,$mois);
	}

	/**
	 * Ajoute une ligne de frais hors forfait dans une fiche donnée
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $mois : le mois de la fiche concernée
	 * @param $lesFrais : les quantités liées à chaque type de frais, sous la forme d'un tableau
	*/
	public function ajouteFrais($idVisiteur, $mois, $uneLigne)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : valider la donnée contenues dans $uneLigne ...

		$dateFrais = $uneLigne['dateFrais'];
		$libelle = $uneLigne['libelle'];
		$montant = $uneLigne['montant'];

		$this->dao->creeLigneHorsForfait($idVisiteur,$mois,$libelle,$dateFrais,$montant);
		$this->dao->recalculeMontantFiche($idVisiteur,$mois);
	}

	/**
	 * Supprime une ligne de frais hors forfait dans une fiche donnée
	 * 
	 * @param $idVisiteur : l'id du visiteur 
	 * @param $mois : le mois de la fiche concernée
	 * @param $idLigneFrais : l'id de la ligne à supprimer
	*/
	public function supprFrais($idVisiteur, $mois, $idLigneFrais)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session et cohérents entre eux

	    $this->dao->supprimerLigneHorsForfait($idLigneFrais);
		$this->dao->recalculeMontantFiche($idVisiteur,$mois);
	}
}