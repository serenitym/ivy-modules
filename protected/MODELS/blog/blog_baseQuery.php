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

class blog_baseQuery extends ivyModule_objProperty
{
    var $blog;
    var $filters;


    function Get_baseQueryRecord()
    {
        /**
         *  blogRecords_view  -- leftOUTER JOIN  (blogRecords cu blogRecords_settings)
         *
         *
         * VIEW blogTagsName_view AS
                 SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
               FROM blogTags
                   JOIN blogMap_recordsTags
                   ON ( blogTags.idTag = blogMap_recordsTags.idTag )
               GROUP BY idRecord
         *
         * -- ATENTIE -- Nu sunt sigura ca este cea mai eficienta metoda cu acest view  (dar mom pare cea mai simpla)
         *
        */

         $query = "SELECT
                     blogRecords_view. idRecord,
                     idCat,
                     idTree,
                     uidRec,
                     title,
                     content,
                     lead,
                     leadSec,
                     country,
                     city,
                     entryDate,
                     publishDate,
                     republish,
                     relatedStory,

                     css,
                     js,
                     SEO,

                     idFormat,
                     format,

                     folderName,
                     idFolder,

                      uid_Rec, fullName,
                      tagsName,

                      uidsCSV,
                      fullNamesCSV,
                      unamesCSV

                      FROM blogRecords_view
                      JOIN
                      (
                          SELECT uid AS uid_Rec, CONCAT(first_name,'  ',last_name) AS fullName
                          from auth_user_details
                      ) AS TBuserName
                      ON (blogRecords_view.uidRec = TBuserName.uid_Rec)

                      LEFT OUTER JOIN
                      (
                          SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
                  		  FROM blogMap_recordsTags
                          GROUP BY idRecord
                      ) AS TBtagsName
                      ON (blogRecords_view.idRecord = TBtagsName.idRecord)

                      LEFT OUTER JOIN
                      (
                          SELECT
                            idRecord,
                            GROUP_CONCAT( blogRecords_authors.uid SEPARATOR ', ' )
                              AS uidsCSV ,
                            GROUP_CONCAT(
                             CONCAT (first_name, ' ', last_name )  SEPARATOR ', '
                            ) AS fullNamesCSV ,

                            GROUP_CONCAT( auth_users.name SEpARATOR ', ')
                              AS unamesCSV

                          FROM blogRecords_authors
                          JOIN auth_user_details
                              ON (blogRecords_authors.uid = auth_user_details.uid)

                          JOIN auth_users
                              ON (blogRecords_authors.uid = auth_users.uid)

                          GROUP BY idRecord
                      ) AS TBauthors
                      ON (blogRecords_view.idRecord = TBauthors.idRecord)



                                           ";

         return $query;

    }

    function Get_baseQueryRecords()
    {

        //modelComm_name,commentsView,commentsStat,commentsApprov,SEO,
        // nrRates,ratingTotal,


        $query = "SELECT
                    blogRecords_view.idRecord,
                    idCat,
                    idTree,
                    uidRec,
                    title,
                    content,
                    lead,
                    leadSec,
                    country,
                    city,
                    entryDate,
                    publishDate,
                    republish,
                    format,
                    relatedStory,
                    folderName,
                    idFolder,

                    uid_Rec,
                    fullName,
                    tagsName,

                    uidsCSV,
                    fullNamesCSV,
                    unamesCSV


                    FROM blogRecords_view
                         JOIN
                         (
                           SELECT uid AS uid_Rec, CONCAT(first_name,'  ',last_name) AS fullName
                           FROM auth_user_details
                         ) AS TBuserName
                         ON (blogRecords_view.uidRec = TBuserName.uid_Rec)

                         LEFT OUTER JOIN
                         (
                           SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
                                FROM blogMap_recordsTags
                                GROUP BY idRecord
                         ) AS TBtagsName
                          ON (blogRecords_view.idRecord = TBtagsName.idRecord)

                          LEFT OUTER JOIN
                          (
                              SELECT
                                idRecord,
                                GROUP_CONCAT( blogRecords_authors.uid SEPARATOR ', ' )
                                  AS uidsCSV ,
                                GROUP_CONCAT(
                                 CONCAT (first_name, ' ', last_name )  SEPARATOR ', '
                                ) AS fullNamesCSV ,

                                GROUP_CONCAT( auth_users.name SEpARATOR ', ')
                                  AS unamesCSV

                              FROM blogRecords_authors
                              JOIN auth_user_details
                                  ON (blogRecords_authors.uid = auth_user_details.uid)

                              JOIN auth_users
                                  ON (blogRecords_authors.uid = auth_users.uid)

                              GROUP BY idRecord
                          ) AS TBauthors
                          ON (blogRecords_view.idRecord = TBauthors.idRecord)

                          ";


     return $query;

    }
    /**
     * @param        $filters  = array($filterName => $filterValue);
     *
     * @return array
     */
    function Get_queryRecords($filters = array(), $query = '')
    {
         $sql = new stdClass();
        //$sql->parts['query'];
        //$sql->fullQuery;

        $sql->parts['query']  = $query ? $query:  $this->Get_baseQueryRecords(); // return string
        $basicFilters         = $this->filters->Get_basicFilter();  //return array
        $requestFilters       = $this->filters->_handle_requestFilters($filters);

        $sql->parts['wheres'] = array_merge($basicFilters, $requestFilters);
        $sql->parts['where']  =  count($sql->parts['wheres']) == 0 ? ''
                                  : ' WHERE '.implode(' AND ', $sql->parts['wheres'])
                                 ;
        $sql->fullQuery       = $sql->parts['query'].
                                $sql->parts['where'];

        /*error_log("[ ivy ] Cblog - Get_queryRecords : "
                  .preg_replace('/\s+/', ' ', $sql->fullQuery)
        );*/

        return $sql;
    }

    function Get_queryRecord()
    {
        $sql = new stdClass();
        //$sql->parts['query'];
        //$sql->fullQuery;

        $sql->parts['query'] = $this->Get_baseQueryRecord();
        $sql->parts['where'] = " WHERE blogRecords_view.idRecord = '{$_GET['idRec']}'" ;

        $sql->fullQuery = implode(' ', $sql->parts);
        error_log("[ ivy ] blog_baseQuery - Get_queryRecord : "
                .preg_replace('/\s+/', ' ', $sql->fullQuery)
        );

        return $sql;

    }


}