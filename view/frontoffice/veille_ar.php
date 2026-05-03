<?php
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';
$vc = new VeilleC();
$stats = $vc->getRegionalMarketStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aptus AR - Holographic Desk</title>
    <!-- A-Frame -->
    <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
    <!-- AR.js for A-Frame -->
    <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js"></script>
    <style>
        body { margin: 0; overflow: hidden; }
        #overlay {
            position: absolute; top: 10px; left: 10px; z-index: 100;
            color: white; background: rgba(0,0,0,0.5); padding: 10px;
            font-family: sans-serif; border-radius: 8px;
        }
    </style>
</head>
<body style="margin: 0; overflow: hidden;">
    <div id="overlay">
        <h3>Aptus Market AR</h3>
        <p>Pointez la caméra vers un marqueur Hiro.</p>
    </div>

    <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;">
        <a-marker preset="hiro">
            <!-- Desk Base -->
            <a-box position="0 0 0" width="3" height="0.1" depth="3" color="#1e1e2f" opacity="0.8"></a-box>
            
            <a-text value="Marchà© Rà©gional" position="-1 0.5 -1" rotation="-90 0 0" color="#fff" width="4"></a-text>

            <?php
            $maxSalary = 5000;
            $xOffset = -1;
            foreach(array_slice($stats, 0, 5) as $stat):
                $height = ($stat['avg_salary'] / $maxSalary) * 2; // scale height
            ?>
                <!-- Bar -->
                <a-box position="<?php echo $xOffset; ?> <?php echo $height/2; ?> 0" 
                       width="0.3" height="<?php echo $height; ?>" depth="0.3" 
                       color="#4f46e5" opacity="0.9"></a-box>
                
                <!-- Label -->
                <a-text value="<?php echo htmlspecialchars($stat['region']); ?>" 
                        position="<?php echo $xOffset; ?> <?php echo $height + 0.1; ?> 0" 
                        align="center" color="#fff" width="2" scale="0.5 0.5 0.5"></a-text>
            <?php 
                $xOffset += 0.5;
            endforeach; 
            ?>
        </a-marker>
        <a-entity camera></a-entity>
    </a-scene>
</body>
</html>
