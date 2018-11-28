<?

$padded_trails_array = array_map(
        function($trail_array) {
            return array_pad($trail_array, -5, null);
        }, $trails);
        
