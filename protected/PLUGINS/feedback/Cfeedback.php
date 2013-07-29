<?php
/**
 * Can Based on yml - config
 * updatePrev:
        title:
 *           pstName: 'otherName'
             validRules:
              string:
                fbk: {type: "warning", name: "News Title", mess: "Your title should be a string" }

              notEmpty:
                 fbk: {type: "error", name: "News Title", mess: "Your news must have a title" }
 *
          newsDate: ""

          extLink: ""

          lead:
             fbk: {type: "error", name: "News Lead", mess: "Your news must have a Lead" }
             varType: "string"

          newsPic: ""
 */
/**
 *** Main feature: mess propertie**
 * - setiing the mess property
 * - mess = [ 0 : {
 *              fbType : 'error/ warning, succes'
 *              fbName : 'anyName',
 *              fbMess : 'anyMessage ...log or short'
 *              fb_readMore : 'if the message is to long'
 *          }
 * ];
 *
 * ** use Case: **
 *
 * + set ass many feedback messages as you want from any module like
 *       $this->C->feedback->Set_mess('error', 'Title', 'You must enter a title !');
 * + recomadation:
 *  make a pointer to this object like
 *      $this->fbk = &$this->C->feedback
 *
 * + use in any template like this:
 * if is templateName.php use $core->Render_Module($core->feedback);
 * if is templateName.html use $this->Render_Module($core->feedback);
 *
 */
class Cfeedback
{
    var $fbkMess = array();
    var $mess = array();

    function Set_mess($fbType,$fbName, $fbMess)
    {

        $fbMess = str_replace('"','',$fbMess);
        $fbMess = str_replace('$','',$fbMess);

        $fb_readMore = strlen($fbMess) > 100 ? true :false;
        array_push(
            $this->mess,
            array('fbType'=>$fbType,
                  'fbName'=>$fbName,
                  'fbMess'=>$fbMess,
                  'fb_readMore' => $fb_readMore)
        );
        // fbk este refacut chiar daca exista in session
        // astfel va fi refacut cu noile mesaje de feedback
        $this->fbkMess = $this->mess;
    }
    function _init_()
    {
        if (isset($_SESSION['feedback'])) {

            // daca exista un feedback care a fost retinut atunci preia
            // fbkMess este cel ce se va afisa in final
            $this->fbkMess = $_SESSION['feedback'];
            $_SESSION['feedback'] = '';
            unset($_SESSION['feedback']);
        }
    }
    function __destruct()
    {
        // retinut feedbackul la destruct
        $_SESSION['feedback'] = $this->mess;
    }

    //============================[ aliasuri , shorcuts ]=======================
    /**
     * Seteaza mesaj pe baza unui array asociativ
     * @param $fbk
     */
    function Set_messFbk($fbk)
    {
        $this->Set_mess($fbk['type'], $fbk['name'], $fbk['mess']);
    }
    /**
     *Seteaza un mesaj si returneaza true sau false = este / nu eroare
     *
     * @param $fbType
     * @param $fbName
     * @param $fbMess
     *
     * @return bool
     */
    function SetGet_badmess($fbType,$fbName, $fbMess)
    {
          $this->Set_mess($fbType, $fbName, $fbMess);
          // daca acest feedback este de eroare
          return $fbType !=  'error'
                 ? true
                 : false;
    }
    /**
     * Alias pt mai SetGet_badmess , primeste un array asociativ cu parametrii
     * @param $fbk
     *
     * @return bool
     */
    function SetGet_badmessFbk($fbk)
    {
        return  $this->SetGet_badmess($fbk['type'], $fbk['name'], $fbk['mess']);
    }
}