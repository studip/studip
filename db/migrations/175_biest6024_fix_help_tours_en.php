<?php

# refers to https://develop.studip.de/trac/ticket/6024

class Biest6024FixHelpToursEn extends Migration {
	
    /**
     * {@inheritdoc}
     */
    public function description() {
        return "Fixes all incorrect references to the help tour system's Continue-Button in the core's 
        		english help tours.";    
    }

    /**
     * {@inheritdoc}
     */
    public function up() {
    	
    	//correct all first help_tour_steps
    	
    	$new_first_steps = [  		
    	    '7af1e1fb7f53c910ba9f42f43a71c723' => "This tour provides an overview of the supplied search options.\r\n\r\To proceed, please click \"Continue\" in the lower-right corner.",
    		'c89ce8e097f212e75686f73cc5008711' => "This tour provides an overview of the participant administration\'s options.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'de1fbce508d01cbd257f9904ff8c3b43' => "This tour provides a general overview of the profile page\'s structure.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'1badcf28ab5b206d9150b2b9683b4cb6' => "This tour provides an overview of the functionality of \"My courses\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'fa963d2ca827b28e0082e98aafc88765' => "This tour provides an overview of the functionality of \"My courses\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'f0aeb0f6c4da3bd61f48b445d9b30dc1' => "This tour provides an overview of the start page\'s features and functions.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'3dbe7099f82dcdbba4580acb1105a0d6' => "This tour provides an overview of the forum\'s administration.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'9e9dca9b1214294b9605824bfe90fba1' => "This tour provides an overview of the creation of study groups to cooperate with fellow students.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'89786eac42f52ac316790825b4f5c0b2' => "This tour provides an overview of the forum\'s elements and options of interaction.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'e41611616675b218845fe9f55bc11cf6' => "This tour shows how to upload a picture in the profile page.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'83dc1d25e924f2748ee3293aaf0ede8e' => "This tour provides an overview of the functionality of \"Blubber\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'588effa83da976a889a68c152bcabc90' => "This tour provides an overview of the functionality of \"Blubber\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'd9913517f9c81d2c0fa8362592ce5d0e' => "This tour provides an overview of the functionality of \"Blubber\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.",
    		'05434e40601a9a2a7f5fa8208ae148c1' => "This tour provides an overview of the personal document manager.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner."
      	];
    	
    	foreach($new_first_steps as $key => $value) {
    	    $update = "UPDATE help_tour_steps
    		    SET tip = '$value' 
      		    WHERE tour_id = '$key' AND 
      		    step = 1 
    		    ";	    	
    	    DBManager::get()->exec($update);
    	}
    }

    /**
     * {@inheritdoc}
     */
    public function down() {
        // processed in 164_help_tours_en.php
    }
}