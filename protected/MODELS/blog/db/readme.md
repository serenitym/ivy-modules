#Data base management

###Update article

* blogRecords
* blogRecords_stats
* blogRecords_settings
* blogRecords_authors
* blogMap_recordsTags
    * delete old tags & insert the new ones
* blogTags
    * replace intro this table

###Insert article

* blogRecords
* blogRecords_stats
* blogRecords_settings

###Delete article

* blogRecords
* blogRecords_stats
* blogRecords_settings
* blogMap_recordsTags

*diferenta dintr insert si Update sunt acele tabele care
se vor joinui cu LEFT OUTER JOIN defapt*

#Blog settings

* **tmplFiles** = [ {idTmpl: '', tmplFile: ''} ]
* **types**     = [ {idType: '', type: '', idTmpl:'', tmplFile: '' } ]
* **folders**   = [ {idFolder: '',parentFolder: '', folderName:'', idTmpl: '',
                   tmplFile: ''  } ]
* **bannedTags** = [{idTag: '', tagName: ''}]





