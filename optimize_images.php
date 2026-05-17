<?php
$directory = 'assets/images/';
$files = scandir($directory);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
        $sourcePath = $directory . $file;
        $destinationPath = $directory . pathinfo($file, PATHINFO_FILENAME) . '.webp';
        
        echo "Converting $file...\n";
        
        // Load the PNG image
        $image = imagecreatefrompng($sourcePath);
        
        if ($image !== false) {
            // Convert to truecolor and enable alpha blending
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            
            // Save as WebP with 80% quality
            imagewebp($image, $destinationPath, 80);
            
            // Free up memory
            imagedestroy($image);
            
            echo "Converted $file to WebP.\n";
            
            // Delete original file
            unlink($sourcePath);
            echo "Deleted original $file.\n";
        } else {
            echo "Failed to load $file.\n";
        }
    }
}
echo "Optimization complete.\n";
?>
