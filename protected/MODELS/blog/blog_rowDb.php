<?php
/**
 * PHP Version 5.3+
 *
 * @category 
 * @package 
 * @author Ioana Cristea <ioana@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link http://serenitymedia.ro
 */

class blog_rowDb
{
    var $blog; // pointer la obiectul principal blog

    function Get_content(&$row, $lenght = 100)      {

         if(!$row['content']){
             $string =    substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['content'];
    }
    function Get_content_noPic(&$row, $lenght = 100){

        if (preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches))
        foreach($matches[0] AS $match)
            $row['content'] = str_replace($match,'',$row['content']);

        #var_dump($matches[0]);

         if($row['content']){
             $string =    substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['content'];
    }
    function Get_leadSec(&$row, $lenght = 70)       {

         if(!$row['leadSec']){

             $string = $row['lead']
                       ? substr(strip_tags($row['lead']),0,$lenght)
                       : substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         } else {
             return $row['leadSec'];
         }
    }
    function Get_recordPics(&$row)                  {
       // preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches);
        //preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $row['content'], $matches);
        preg_match_all('/(?<=\<img).+src=[\'"]([^\'"]+)/i', $row['content'], $matches);
        //echo "<b>Get_record_mainPic : matches </b>";
        //var_dump($matches);
        return $matches;

    }
    function Get_record_mainPic(&$row)              {
         #====================================[ main Pic ]===========================================================================
        /*$row['record_mainPic_src'] = preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches)
                                 ? $matches[1][0]
                                 : "";*/
        $matches = $this->Get_recordPics($row);

        if ($matches[1]) {
            return  $matches[1][0];
        }

        # echo $row['title']."<br>".var_dump($matches)."<br>";
    }
    function Get_record_href(&$row)                 {

       return "index.php?idT={$row['idTree']}".
                 "&idC={$row['idCat']}".
                 "&idRec={$row['idRecord']}".
                 //"&type={$currentLayout}".
                  ($row['format'] && $row['format']!='blog' ? "&recType={$row['format']}" : '');

    }
    function Get_record_authorHref($uid)            {

        return "index.php?idT=3".
                         "&idC=3".
                         "&uid={$uid}";

    }
    function Get_record_hrefFolderFilter($idFolder) {

        // using a method from anothe blog object = filters

        $href = "?idT={$this->idTree}&idC={$this->idNode}&"
                .$this->blog->filters->hrefFilter('idFolder', $idFolder);
        return  $href;

    }
    function Get_tagsArray($tagsName)               {
        $tags = str_getcsv($tagsName, ',');
        foreach($tags AS $key=>$tag) {
            $tags[$key] = trim($tag);
        }

        return $tags;
    }
    function Get_publishedStatus ($publishDate)     {
        return $publishDate ? 'record-published' : 'record-unpublished';

    }
    function Get_recordHrefHome($row)               {
        // cine este tmpIdTree??
        return  "index.php?idT={$this->blog->tmpIdTree}".
                  "&idC={$row['idCat']}".
                  "&idRec={$row['idRecord']}".
                   ($row['format'] && $row['format']!='blog' ? "&recType={$row['format']}" : '');
    }
    function Get_authors($uids, $fullNames)         {
        $authors    = array();
        foreach ($uids AS $key => $uid) {
            array_push($authors, array(
                    "uid" => $uid,
                    "fullName" => $fullNames[$key],
                    "authorHref" => $this->Get_record_authorHref($uid)
                ));
        }
        //var_dump( $authors);

        return $authors;
    }

    function Get_rights_articleEdit($uidRec, $uids = array())
    {
        $editRight = ($this->C->user->rights['article_edit'] )
                      ||  $uidRec == $this->C->user->uid
                      || in_array($this->C->user->uid, $uids) ;


        /*echo "Userul articles_edit = ".$this->C->user->rights['article_edit']
              . " autor({$uidRec}) = ". $this->C->user->uid. " " .($uidRec == $this->C->user->uid ? 'DA' : "NU");*/
        //var_dump($this->C->user->rights);
        return $editRight;
    }
    /**
     * Daca este un user logat
     *      - daca are permisiuni de master poate edita
     *      - daca nu are permisiuni
     *              - si este autorul recordului  - poate edita
     *
     */
    function Get_recordED($uidRec, $uids = array())
    {
        $editRight = $this->Get_rights_articleEdit($uidRec, $uids);
        //var_dump($this->user->rights);
        // error_log("[ ivy ] ACblog - Get_recordED pt {$uidRec} permisiuni = {$editRight} ");

        return !$editRight ? 'not' :'';
    }
}