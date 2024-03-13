<?php namespace App\Models;

use CodeIgniter\Model;
use \App\Models\Tools;

/**
 * Modèle qui implémente les fonctions d'accès aux données 
*/
class DataAccess extends Model {
// TODO : Transformer toutes les requêtes en requêtes paramétrées

	protected $db;

    function __construct()
    {
        parent::__construct();
		$this->db = \Config\Database::connect();
    }

    /**
	 * Retourne les informations d'un visiteur
	 * 
	 * @param $login 
	 * @param $mdp
	 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
	*/
	public function getInfosVisiteur($login, $mdp){
		$req = "SELECT visiteur.id AS id, visiteur.nom AS nom, visiteur.prenom AS prenom, roles.nomRole AS nomRole
				FROM visiteur 
				JOIN roles ON visiteur.idRole=roles.idRole
				WHERE visiteur.login=? AND visiteur.mdp=?";
		$rs = $this->db->query($req, array ($login, $mdp));
		$ligne = $rs->getFirstRow('array'); 
		return $ligne;
	}

	/**
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
	 * concernées par les deux arguments
	 * La boucle foreach ne peut être utilisée ici car on procède
	 * à une modification de la structure itérée - transformation du champ date-
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
	*/
	public function getLesLignesHorsForfait($idVisiteur,$mois){

		$req = "SELECT * 
				FROM lignefraishorsforfait 
				WHERE lignefraishorsforfait.idvisiteur ='$idVisiteur' 
					AND lignefraishorsforfait.mois = '$mois' ";	
		$rs = $this->db->query($req);
		$lesLignes = $rs->getResultArray();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  Tools::dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
		
	// /**
	 // * Retourne le nombre de justificatif d'un visiteur pour un mois donné
	 // * 
	 // * @param $idVisiteur 
	 // * @param $mois sous la forme aaaamm
	 // * @return le nombre entier de justificatifs 
	// */
	// public function getNbjustificatifs($idVisiteur, $mois){
		// $req = "select fichefrais.nbjustificatifs as nb 
				// from  fichefrais 
				// where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		// $rs = $this->db->query($req);
		// $laLigne = $rs->result_array();
		// return $laLigne['nb'];
	// }
		
	/**
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
	 * concernées par les deux arguments
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
	*/
	public function getLesLignesForfait($idVisiteur, $mois){
		$req = "SELECT fraisforfait.id AS idfrais, fraisforfait.libelle AS libelle, lignefraisforfait.quantite AS quantite 
				FROM lignefraisforfait INNER JOIN fraisforfait 
					ON fraisforfait.id = lignefraisforfait.idfraisforfait
				WHERE lignefraisforfait.idvisiteur ='$idVisiteur' AND lignefraisforfait.mois='$mois' 
				ORDER BY lignefraisforfait.idfraisforfait";	
		$rs = $this->db->query($req);
		$lesLignes = $rs->getResultArray();
		return $lesLignes; 
	}
		
	/**
	 * Retourne tous les FraisForfait
	 * 
	 * @return un tableau associatif contenant les fraisForfaits
	*/
	public function getLesFraisForfait(){
		$req = "SELECT fraisforfait.id AS idfrais, libelle, montant FROM fraisforfait ORDER BY fraisforfait.id";
		$rs = $this->db->query($req);
		$lesLignes = $rs->getResultArray();
		return $lesLignes;
	}
	
	/**
	 * Met à jour la table ligneFraisForfait pour un visiteur et
	 * un mois donné en enregistrant les nouveaux montants
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
	*/
	public function majLignesForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "UPDATE lignefraisforfait 
					SET lignefraisforfait.quantite = $qte
					WHERE lignefraisforfait.idvisiteur = '$idVisiteur' 
						AND lignefraisforfait.mois = '$mois'
						AND lignefraisforfait.idfraisforfait = '$unIdFrais'";
			$this->db->simpleQuery($req);
		}
	}
		
	// /**
	 // * met à jour le nombre de justificatifs de la table ficheFrais
	 // * pour le mois et le visiteur concerné
	 // * 
	 // * @param $idVisiteur 
	 // * @param $mois sous la forme aaaamm
	// */
	// public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs){
		// $req = "update fichefrais 
				// set nbjustificatifs = $nbJustificatifs 
				// where fichefrais.idvisiteur = '$idVisiteur' 
					// and fichefrais.mois = '$mois'";
		// $this->db->simpleQuery($req);	
	// }
		
	/**
	 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return vrai si la fiche existe, ou faux sinon
	*/	
	public function existeFiche($idVisiteur,$mois)
	{
		$ok = false;
		$req = "SELECT COUNT(*) AS nblignesfrais 
				FROM fichefrais 
				WHERE fichefrais.mois = '$mois' AND fichefrais.idvisiteur = '$idVisiteur'";
		$rs = $this->db->query($req);
		$laLigne = $rs->getFirstRow('array');
		if($laLigne['nblignesfrais'] != 0){
			$ok = true;
		}
		return $ok;
	}
	
	/**
	 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
	 * L'état de la fiche est mis à 'CR'
	 * Les lignes de frais forfait sont affectées de quantités nulles et du montant actuel de FraisForfait
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	*/
	public function creeFiche($idVisiteur,$mois){
		$req = "INSERT INTO fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
				VALUES('$idVisiteur','$mois',0,0,NOW(),'CR')";
		$this->db->simpleQuery($req);
		$lesFF = $this->getLesFraisForfait();
		foreach($lesFF as $uneLigneFF){
			$unIdFrais = $uneLigneFF['idfrais'];
			$montantU = $uneLigneFF['montant'];
			$req = "INSERT INTO lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite, montantApplique) 
					VALUES('$idVisiteur','$mois','$unIdFrais',0, $montantU)";
			$this->db->simpleQuery($req);
		}
	}

	/**
	 * Signe une fiche de frais en modifiant son état de "CR" à "CL"
	 * Ne fait rien si l'état initial n'est pas "CR"
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	*/
	public function signeFiche($idVisiteur,$mois){
		// met à 'CL' son champs idEtat
		$laFiche = $this->getLesInfosFicheFrais($idVisiteur,$mois);
		if($laFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $mois,'CL');
		}
	}

	/**
	 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
	 * à partir des informations fournies en paramètre
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $libelle : le libelle du frais
	 * @param $date : la date du frais au format français jj//mm/aaaa
	 * @param $montant : le montant
	*/
	public function creeLigneHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
		$dateFr = Tools::dateFrancaisVersAnglais($date);
		$req = "INSERT INTO lignefraishorsforfait 
				VALUES('','$idVisiteur','$mois','$libelle','$dateFr','$montant')";
		$this->db->simpleQuery($req);
	}
		
	/**
	 * Supprime le frais hors forfait dont l'id est passé en argument
	 * 
	 * @param $idFrais 
	*/
	public function supprimerLigneHorsForfait($idFrais){
		$req = "DELETE FROM lignefraishorsforfait 
				WHERE lignefraishorsforfait.id =$idFrais ";
		$this->db->simpleQuery($req);
	}

	// /**
	 // * Retourne les mois pour lesquel un visiteur a une fiche de frais
	 // * 
	 // * @param $idVisiteur 
	 // * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
	// */
	// public function getLesMoisDisponibles($idVisiteur){
		// $req = "select fichefrais.mois as mois 
				// from  fichefrais 
				// where fichefrais.idvisiteur ='$idVisiteur' 
				// order by fichefrais.mois desc ";
		// $rs = $this->db->query($req);
		// $lesMois =array();
		// $laLigne = $rs->getFirstRow('array');
		// while($laLigne != null)	{
			// $mois = $laLigne['mois'];
			// $numAnnee = substr( $mois,0,4);
			// $numMois = substr( $mois,4,2);
			// $lesMois["$mois"] = array(
				// "mois"=>"$mois",
				// "numAnnee"  => "$numAnnee",
				// "numMois"  => "$numMois"
			 // );
			// $laLigne = $rs->next_row('array'); 		
		// }
		// return $lesMois;
	// }

	/**
	 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
	*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "SELECT ficheFrais.idEtat AS idEtat, ficheFrais.dateModif AS dateModif, 
					ficheFrais.nbJustificatifs AS nbJustificatifs, ficheFrais.montantValide AS montantValide, etat.libelle AS libEtat 
				FROM  fichefrais INNER JOIN Etat ON ficheFrais.idEtat = Etat.id 
				WHERE fichefrais.idvisiteur ='$idVisiteur' AND fichefrais.mois = '$mois'";
		$rs = $this->db->query($req);
		$laLigne = $rs->getFirstRow('array');
		return $laLigne;
	}

	/**
	 * Modifie l'état et la date de modification d'une fiche de frais
	 * 
	 * @param $idVisiteur 
	 * @param $mois sous la forme aaaamm
	 * @param $etat : le nouvel état de la fiche 
	 */
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "UPDATE ficheFrais 
				SET idEtat = '$etat', dateModif = NOW() 
				WHERE fichefrais.idvisiteur ='$idVisiteur' AND fichefrais.mois = '$mois'";
		$this->db->simpleQuery($req);
	}
	
	/**
	 * Obtient toutes les fiches (sans détail) d'un visiteur donné 
	 * 
	 * @param $idVisiteur 
	*/
	public function getFiches ($idVisiteur) {
		$req = "SELECT idVisiteur, mois, montantValide, dateModif, id, libelle
				FROM  fichefrais INNER JOIN Etat ON ficheFrais.idEtat = Etat.id 
				WHERE fichefrais.idvisiteur = '$idVisiteur'
				ORDER BY mois DESC";
		$rs = $this->db->query($req);
		$lesFiches = $rs->getResultArray();
		return $lesFiches;
	}
	
	/**
	 * Calcule le montant total de la fiche pour un visiteur et un mois donnés
	 * 
	 * @param $idVisiteur 
	 * @param $mois
	 * @return le montant total de la fiche
	*/
	public function totalFiche ($idVisiteur, $mois) {
		// obtention du total hors forfait
		$req = "SELECT SUM(montant) AS totalHF
				FROM  lignefraishorsforfait 
				WHERE idvisiteur = '$idVisiteur'
					AND mois = '$mois'";
		$rs = $this->db->query($req);
		$laLigne = $rs->getFirstRow('array');
		$totalHF = $laLigne['totalHF'];
		
		// obtention du total forfaitisé
		$req = "SELECT SUM(montantApplique * quantite) AS totalF
				FROM  lignefraisforfait 
				WHERE idvisiteur = '$idVisiteur'
					AND mois = '$mois'";
		$rs = $this->db->query($req);
		$laLigne = $rs->getFirstRow('array');
		$totalF = $laLigne['totalF'];

		return $totalHF + $totalF;
	}

	/**
	 * Modifie le montantValide et la date de modification d'une fiche de frais
	 * 
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : mois sous la forme aaaamm
	 */
	public function recalculeMontantFiche($idVisiteur,$mois){
	
		$totalFiche = $this->totalFiche($idVisiteur,$mois);
		$req = "UPDATE ficheFrais 
				SET montantValide = '$totalFiche', dateModif = NOW() 
				WHERE fichefrais.idvisiteur ='$idVisiteur' AND fichefrais.mois = '$mois'";
		$this->db->simpleQuery($req);
	}
}
?>