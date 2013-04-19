<?php


class PROMO
{
    var $DB;

    var $price;
    var $promo;
    var $end_promo;
    var $new;
    var $end_new;

    var $display='';
    var $idPR;

    function saveExtras()               {

        $this->idPR =$idPR = $_POST['idPR'];

        $this->promo       = $promo       = $_POST['promo'];
        $this->end_promo   = $end_promo   = $_POST['end_promo'];            //date to be formated in ISO8601 for MySQL

        $this->new         = $new         = $_POST['new'];                  //yes || no
        $this->end_new     = $end_new     = $_POST['end_new'];              //date to be formated in ISO8601 for MySQL

        $cont='';
    //__________________________________________________________________________________________________________________

        if($promo && $end_promo)
        {
            $DB_endPromo = date('c', strtotime($end_promo));
            $q = "UPDATE products SET promo='{$promo}', end_promo='{$DB_endPromo}' where id='{$idPR}' ";

            if( $this->CHECK_valability($end_promo)) $this->DB->query($q);
            else  $this->RESET_DB('promo'); /*$cont .="NO PASSING \n";*/
            $cont .=$q."\n";
        }
        else $this->RESET_DB('promo');
    //__________________________________________________________________________________________________________________

        if($new=='yes' && $end_new)
        {
            $DB_endNew = date('c', strtotime($end_new));
            $q = "UPDATE products SET new='1', end_new='{$DB_endNew}' where id='{$idPR}' ";

            if($this->CHECK_valability($end_new)) $this->DB->query($q);
            else $this->RESET_DB('new'); /*$cont .="NO PASSING \n";*/

            $cont .=$q."\n";
        }
        else $this->RESET_DB('new');


        file_put_contents('testEXTRAS.txt',$cont);

    }

    function RESET_DB($type)            {

        $id=$this->idPR;
        $this->DB->query("UPDATE products SET end_{$type}=NULL , {$type}=NULL WHERE id='{$id}' ");
    }
    //start_ , promo/new, id-ul produsului - daca nu mai este valabila elimina promotia sau NEWstatus din BD
    function CHECK_valability($endDATE) {


        $TMSP_end = strtotime($endDATE);
        $TMSP_today = mktime();

        if($TMSP_end > $TMSP_today)  return true;
        else return false;
    }
    function SET_defaults($days)        {

        $TMSP  =($days*24*60*60)+ mktime();
        return date('d-m-Y',$TMSP);
    }
    function DISPLAY()                  {

        $this->idPR =$idPR = $_POST['idPR'];

        $query = "SELECT new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new,price, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo , name_ro  FROM products where id = '{$idPR}'";
        $res = $this->DB->query($query);

        if($res)
        {
            $rez = $res->fetch_assoc();

           $this->price       = $price       = $rez['price'];
           $this->promo       = $promo       = $rez['promo'];
           $this->end_promo   = $end_promo   = $rez['end_promo'];
           $this->name        = $name        = $rez['name_ro'];

           $this->new         = $new         = $rez['new'];
           $this->end_new     = $end_new     = $rez['end_new'];

        //_________________________________________[ //CHECK valability ]________________________________________

            /*if($end_promo) {
                $end_promo = date('d-m-Y',strtotime($end_promo));
                if(!$this->CHECK_valability($end_promo)) { $end_promo='';$this->RESET_DB('promo');}
               }*/


            if($end_promo && !$this->CHECK_valability($end_promo) ) { $end_promo='';$this->RESET_DB('promo');}
            if(($end_new || $new)   && !$this->CHECK_valability($end_new)   ) { $end_new='';  $this->RESET_DB('new');}

        //________________________________[ SET DEFAULTS if NECESARY ]____________________________________________
            if(!$end_promo){$end_promo=$this->SET_defaults(30); $promo='';}
            if(!$end_new){$end_new=$this->SET_defaults(14); $new='';}

        //________________________________[ SET RADIO BUT ]_______________________________________________________
            $NEWon=($new ?  'checked' : '');
            $NEWof=(!$new ? 'checked' : '');
        //________________________________________________________________________________________________________

            $disp = "
                    <p id='Denumire'>{$name}</p>
                    <br/>
                    <div id='REDUCERIcont'>
                        <b>Pret: {$price}</b> &nbsp;&nbsp;&nbsp;
                        Pret promo : <input type='text' name='pret_promo' value='$promo'>&nbsp;&nbsp;&nbsp;&nbsp;
                        Terminate at : <input type='text' name='end_promo' value='$end_promo'>&nbsp;&nbsp;


                        <br/><br/><br/>
                        <b>New:</b>&nbsp;&nbsp;

                        <input type='radio' name='NEWstatus' value='no' $NEWof > End  &nbsp;&nbsp;
                        <input type='radio' name='NEWstatus' value='yes' $NEWon> Begin &nbsp;&nbsp;
                         Terminate at : <input type='text' name='end_new' value='$end_new'>&nbsp;&nbsp;
                         <form action='' method='post' id='BLANCKform_PROMO'>
                          <input type='button' name='save_promo' value='save' onclick=\"savePromo('{$idPR}')\" />
                         </form>

                        <div class='showINDICATII'>Indicatii</div>
                        <div class='indicatii'>
                            <br/> * PROMOTII
                            <br/> * - pentru a sterge o promotie - stergeti suma redusa
                            <br/> * - default-ul pentru data la care se incheie promotia este o luna de la data introducerii acesteia
                            <br/> *
                            <br/> * NEW
                            <br/> * - un produs va avea statusul de new la introducerea acestuia
                            <br/> * - acest status se va incheia prestabilit la o saptamana de la intruducere
                            <br/> * - pentru a termina mai devreme acest status selectati <b>End</b>
                            <br/> * - pentru a repune statusul de new pe un produs selectati <b>Begin</b>
                            <br/>    si alegeti o data de terminare a acestui status
                        </div>

                    </div>

                ";
            return $disp;
        }
        else {return 'Nu exista inregistrare aici';}
    }
    function parsePOST()                {

        if(isset($_POST['display']))$this->display = true;
        if(isset($_POST['saveExtras'])) $this->saveExtras();
    }
    function __construct()
    {
        $this->DB = new mysqli(dbHost,dbUser,dbPass,dbName);
        $this->parsePOST();
    }
}

$promo_new = new PROMO();
if($promo_new->display) echo $promo_new->DISPLAY();