<?php
/**
 * Plugin Favicon
 *
 * @package	PLX
 * @version	1.0
 * @date	03/08/2011
 * @author	Cyril MAGUIRE
 **/
class favicon extends plxPlugin {

	/**
	 * Constructeur de la classe favicon
	 *
	 * @param	default_lang	langue par d?faut utilis?e par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);
		
		# D?clarations des hooks
		$this->addHook('ThemeEndHead', 'addFavicon');
		$this->addHook('AdminTopEndHead', 'addFavicon');		
		$this->addHook('AdminAuthEndHead', 'addFavicon');		
		$this->addHook('plxShowLastCatList', 'plxShowLastCatList');		
	}

	/**
	 * M?thode qui ajoute l'insertion des liens vers les favicons dans la partie <head> du site
	 *
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/	
	public function addFavicon() {
		
		echo "\t".'<!-- FAVICONS -->'."\n";
		echo "\t".'<link href="'.PLX_PLUGINS.'favicon/img/favicon.ico" type="image/x-icon" rel="icon" />'."\n";
		echo "\t".'<link href="'.PLX_PLUGINS.'favicon/img/favicon.ico" type="image/x-icon" rel="shortcut icon" />'."\n";
		echo "\t".'<link href="'.PLX_PLUGINS.'favicon/img/apple-touch-icon.png" type="image/apple-touch-icon" rel="apple-touch-icon" />'."\n";

	}

	/**
	 * Méthode qui affiche la liste des catégories actives, avec la liste des articles associés.
	 * Si la variable $extra est renseignée, un lien vers la
	 * page d'accueil (nommé $extra) sera mis en place en première
	 * position.
	 *
	 * @param	$extra	string nom du lien vers la page d'accueil
	 * @param	$format	string format du texte pour chaque catégorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #art_nb)
	 *			ou
	 *			$format array(
	 *					$format[0] string format du texte pour chaque catégorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #cat_img,
	 *					#art_nb)
	 * 					$format[1] integer nombre d'articles maximum à afficher dans les sous-menu
	 * 					$format[2] array('src'=> 'val', 'dim'=>'val') tableau contenant l'url et les dimensions d'une image de remplacement de 
	 *					l'intitulé de l'index
	 *					$format[3] string (oui ou non) permet l'affichage des catégories qui ne sont pas affichées dans le menu
	 *			)
     * @param	include string	liste des catégories à afficher séparées par le caractère | (exemple: 001|003)
     * @param	exclude string	liste des catégories à ne pas afficher séparées par le caractère | (exemple: 002|003)
 	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F, Cyril MAGUIRE
	 */
	public function plxShowLastCatList(){
		$string =<<<END
		# Si le mode est protégé (password), on repasse en mode standard le temps de l'affichage du menu
		if ( strpos(\$this->mode(),'_password') !== false) {
			\$pass = true;
			# Suppression du s final, si applicable
			\$s = (strpos(\$this->mode(),'s_') !== false) ? 's_' : '_';
			\$this->plxMotor->mode = str_replace(\$s.'password', '', \$this->mode());
		} else {
			\$pass = false;
			\$s = null;
		}

		if (is_array(\$format)) {
			\$f = \$format[0];
			\$nbSousMenu = intval(\$format[1]);
			if (isset(\$format[2]) && is_array(\$format[2])) {
				if (isset(\$format[2]['src'])) {
					\$src = \$format[2]['src'];
				}
				if (isset(\$format[2]['dim'])) {
					\$dim = \$format[2]['dim'];
				} else {
					\$dim = '32';
				}
			}
			if (isset(\$format[3])) {
				\$vmenu = \$format[3];
			}
			\$format = \$f;
			unset(\$f);
		} else {
			\$nbSousMenu = 0;
		}
		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec \$extra comme nom)
		if(\$extra != '') {
			\$name = str_replace('#cat_id','cat-home',\$format);
			\$name = str_replace('#cat_url',\$this->plxMotor->urlRewrite(),\$name);
			\$name = str_replace('#cat_name',plxUtils::strCheck(\$extra),\$name);
			if (isset(\$src)) {
				\$name = str_replace('#cat_img','<img src="'.\$src.'" width="'.\$dim.'" height="'.\$dim.'" title="'.plxUtils::strCheck(\$extra).'"  alt="'.plxUtils::strCheck(\$extra).'" />',\$name);
			} else {
				\$name = str_replace('#cat_img',plxUtils::strCheck(\$extra),\$name);
			}
			\$name = str_replace('#cat_status',((\$this->catId()=='home' && \$this->mode() == 'home')?'active':'noactive'), \$name);
			\$name = str_replace('#art_nb','',\$name);
			echo \$name."\n\t\t\t";
		}
		if(\$this->plxMotor->aCats) {
			\$format = str_replace('#cat_img', '#cat_name', \$format);
			foreach(\$this->plxMotor->aCats as \$k=>\$v) {
				if (isset(\$vmenu)) {
					if (\$v['menu'] == 'oui')
						\$v['menu'] = 'non';
					else
						\$v['menu'] = 'oui';
				} 
				\$in = (empty(\$include) OR preg_match('/('.\$include.')/', \$k));
				\$ex = (!empty(\$exclude) AND preg_match('/('.\$exclude.')/', \$k));
				if(\$in AND !\$ex) {
				if( (\$v['articles']>0 OR \$this->plxMotor->aConf['display_empty_cat']) AND (\$v['menu']=='oui') AND \$v['active']) { # On a des articles
					\$k = intval(\$k);
					if (\$nbSousMenu != 0) :
					ob_start();
					\$this->lastArtList('<li class="#art_status"><a href="#art_url"><span>#art_title</span></a></li>',\$nbSousMenu,\$k);
					\$sousmenu = ob_get_clean();
					if (strlen(\$sousmenu) != 0):
			            \$sousmenu = '
				<ul>
					'.str_replace('</li><li', '</li>'."\n\t\t\t\t\t".'<li', \$sousmenu).'
				</ul>
			</li>';
					endif;
					else :
						\$sousmenu = '</li>';
					endif;
					# On modifie nos motifs
						\$name = str_replace('#cat_id','menu_cat-'.\$k,\$format);
						\$name = str_replace('#cat_url',\$this->plxMotor->urlRewrite('?categorie'.\$k.'/'.\$v['url']),\$name);
						\$name = str_replace('#cat_name',plxUtils::strCheck(\$v['name']),\$name);
						if ( \$this->mode() == 'article' && in_array(\$k,explode(',',\$this->plxMotor->plxRecord_arts->f('categorie')))) {
							\$name = str_replace('#cat_status','active', \$name);
						}else {
							\$name = str_replace('#cat_status',(\$this->catId()==\$k?'active':'noactive'), \$name);
						}
						\$name = str_replace('#art_nb',\$v['articles'],\$name);
						\$name = str_replace('</li>',\$sousmenu,\$name);
						echo \$name;
					}
				}
			} 
		}
		# Si le mode initial était protégé, on le protège à nouveau
		if (\$pass) {
			\$this->plxMotor->mode = \$this->plxMotor->mode.\$s.'password';
		}
		return true;
END;
		echo '<?php '.$string.'?>';
	}


}
?>