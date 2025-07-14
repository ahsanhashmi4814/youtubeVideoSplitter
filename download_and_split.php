<?php

$videoUrl = 'https://www.youtube.com/watch?v=EEq_-gWlQ0A'; // Replace with actual video URL
$outputDir = "videos";

if (!is_dir($outputDir)) {
   // mkdir($outputDir, 0755, true);
}

// Step 1: Download the video using yt-dlp
echo "Downloading video...\n";
$downloadCmd = "yt-dlp -f best -o \"{$outputDir}/video.%(ext)s\" " . escapeshellarg($videoUrl);
exec($downloadCmd, $outputOutput, $downloadStatus);

if ($downloadStatus !== 0) {
    echo "Error downloading video:\n";
    print_r($outputOutput);
    exit(1);
}

// Step 2: Find the downloaded video file
$videoPath = null;
$files = scandir($outputDir);
foreach ($files as $file) {
    if (preg_match('/video\.(mp4|mkv|webm)$/i', $file)) {
        $videoPath = $outputDir . '/' . $file;
        break;
    }
}

if (!$videoPath || !file_exists($videoPath)) {
    die("Downloaded video not found.\n");
}

// Step 3: Get the duration of the video
echo "Getting video duration...\n";
$ffprobeCmd = "ffprobe.exe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($videoPath);
$duration = (float) shell_exec($ffprobeCmd);

if ($duration <= 0) {
    die("Could not determine video duration.\n");
}

// Step 4: Split the video into 30-second clips
$clipLength = 30; // seconds
$totalClips = ceil($duration / $clipLength);

echo "Splitting into {$totalClips} clips...\n";


for ($i = 0; $i < $totalClips; $i++) {
    $start = $i * $clipLength;
    $outputClip = "{$outputDir}/clip_" . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . ".mp4";
    
     $ffmpegCmd = "ffmpeg.exe -y -i " . escapeshellarg($videoPath) .
                 " -ss {$start} -t {$clipLength} -c copy " . escapeshellarg($outputClip);

    exec($ffmpegCmd, $splitOutput, $splitStatus);
    
    if ($splitStatus !== 0) {
        echo "Error creating clip ".$i + 1;
        print_r($splitOutput);
    } else {
        echo "Created: {$outputClip}\n";
    }
}

echo "✅ Done.\n";
