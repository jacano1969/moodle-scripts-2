<?php
	
require_once('config.php');
require_login(); 

if (!isadmin()) {
    error("Only the administrator can access this page!", $CFG->wwwroot);
}


/* -------------------- Settings ---------------------- */
$search    = 'domain.com/vle';
$replace   = 'vle.domain.com';

$case_sensitive = false; // false = Match 'vle' and 'VLE'
/* ---------------------------------------------------- */


// Find non-empty block instances
if ($records = get_records_select('block_instance', 'configdata != "" AND configdata != "Tjs="')) {

    echo "<h2>Replacing '<span style=\"color:red;\">$search</span>' with '<span style=\"color:green;\">$replace</span>' inside block content.</h2>";

    $no_found = count($records);
    echo "<h3>$no_found non-empty block instances found</h3>";
    
    $c = 1;
    $found_count = 0;
    
    echo '<pre>';
    foreach ($records as $record) {
        
        echo "<p><strong>Block $c</strong> - ";

        $configdata = unserialize(base64_decode($record->configdata));
        if ($configdata->text !== NULL) {
        
            // Convert HTML tags to character entities to allow URL replacement inside links
            $html = htmlspecialchars($configdata->text);
            $string_found = ($case_sensitive) ? strpos($html, $search) : stripos($html, $search);
            
            if ($string_found === true) {
            
                $updated_content = ($case_sensitive) ? htmlspecialchars_decode(str_replace($search, $replace, $html)) : htmlspecialchars_decode(str_ireplace($search, $replace, $html));
                $configdata->text = $updated_content;
                // New configdata
                $new_content = base64_encode(serialize($configdata));
                $record->configdata = $new_content;
                
                if (update_record('block_instance', $record)) {
                    echo '<strong style="color:green;">Found and replaced!</strong>';
                    $found_count++;
                } else {
                    echo 'FAILed to update block_instance :*(';
                }
                
            } else {
                echo "Search string not found";
            }
            
        } else {
            // Nothing inside block's content area.
            echo 'No content';
        }
        $c++;
        
        echo '<p>';
        
    }
    echo '</pre>';
    
    echo "<h3>$found_count blocks updated to use the string '$replace' instead of '$search'.</h3>";

} else {
    print_heading('FAIL!');
    echo '<p>No block instances found.</p>';
}

?>
