<?= $this->extend('l_visiteur') ?>

<?= $this->section('body') ?>
<div id="contenu">
	<h2>Renseigner ma fiche de frais du mois <?= substr($mois,4,2)."-".substr($mois,0,4) ?></h2>
					
	<div class="corpsForm">
	  
		<fieldset>
			<legend>Eléments forfaitisés</legend>
			<?php
				foreach ($fiche['lesFraisForfait'] as $unFrais)
				{
					$idFrais = $unFrais['idfrais'];
					$libelle = $unFrais['libelle'];
					$quantite = $unFrais['quantite'];

					echo 
					'<p>
						<label for="'.$idFrais.'">'.$libelle.'</label>
						<input type="text" id="'.$idFrais.'" name="lesFrais['.$idFrais.']" size="10" maxlength="5" value="'.$quantite.'" />
					</p>
					';
				}
			?>
		</fieldset>
		<p></p>
	</div>
	
	<table class="listeLegere">
		<caption>Descriptif des éléments hors forfait</caption>
		<tr>
			<th >Date</th>
			<th >Libellé</th>  
			<th >Montant</th>  
		</tr>
          
		<?php    
			foreach($fiche['lesFraisHorsForfait'] as $unFraisHorsForfait) 
			{
				$date = $unFraisHorsForfait['date'];
				$libelle = $unFraisHorsForfait['libelle'];
				$montant=$unFraisHorsForfait['montant'];
				$id = $unFraisHorsForfait['id'];
				echo 
				'<tr>
					<td class="date">'.$date.'</td>
					<td class="libelle">'.$libelle.'</td>
					<td class="montant">'.$montant.'</td>
				</tr>';
			}
		?>	  
                                          
    </table>

</div>
<?= $this->endSection() ?>
