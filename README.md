## Scripts for Moodle (1.9)

### update_block_instances.php
This script updates URLs stored inside HTML blocks in Moodle.   
Results are displayed in the browser.

### Usage
1. Download `update_block_instances.php` and place it in the root of your Moodle installation (same place as `config.php`).
2. Update the settings:
<pre>
    /* -------------------- Settings ---------------------- */
    $search    = 'domain.com/VLE';
    $replace   = 'vle.domain.com';

    $case_sensitive = false; // false = Match 'vle' and 'VLE'
    /* ---------------------------------------------------- */
</pre>
 * `$search` = your old URL
 * `$replace` = your new URL
 * `$case_sensitive`. If false (default) a search for 'vle' will match both 'vle' and 'VLE'.
3. Run it by pointing your browser to: `http://your-moodle.com/update_block_instances.php`


### Notes
* You must be logged in as a Moodle admin to run this script.  
* Can be used to update text inside HTML blocks also.

### Why?
The college I work at changed the URL of our Moodle from `domain.com/vle` to `vle.domain.com`.  
To update links stored in our Moodle database we did an SQL dump of our Moodle database then ran the following `sed` command to replace old URLs with the new URL: `sed -e 's/domain.com\/vle/vle.domain.com/gi' oldmysqldump.sql > newmysqldump.sql`  
This successfully updated URLs inside our Moodle database. It did NOT update links inside HTML blocks. Turns out HTML blocks are stored inside the `block_instance` and `block_pinned` tables as serialized block objects which are then base64_encoded. Our `sed` command cannot update base64_encoded content.
  
This script finds non-empty HTML block content - note does not update HTML block titles but it could with a small modification (change `$configdata->text` to `$configdata->title`).
It base64_decodes, then unserializes the content to get the block object. We use `htmlspecialchars` to allow us to update HTML links in the content. We then replace any found `$search` with `$replace`. We serialize the block object, base64_encode it then update the block instance in the database.  

