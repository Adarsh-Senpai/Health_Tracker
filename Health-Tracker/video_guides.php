<?php
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Check if user is premium
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_premium FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user['is_premium']) {
    header("Location: premium.php");
    exit();
}

// Video categories and their content
$video_categories = [
    'beginner' => [
        [
            'title' => 'Perfect Push-Up Form',
            'thumbnail' => 'https://img.youtube.com/vi/IODxDxX7oi4/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/IODxDxX7oi4',
            'duration' => '5:30',
            'difficulty' => 'Beginner',
            'description' => 'Master the perfect push-up form with this comprehensive guide. Learn proper technique, common mistakes to avoid, and variations for all fitness levels.'
        ],
        [
            'title' => 'Proper Squat Technique',
            'thumbnail' => 'https://img.youtube.com/vi/YaXPRqUwItQ/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/YaXPRqUwItQ',
            'duration' => '6:15',
            'difficulty' => 'Beginner',
            'description' => 'Learn the fundamentals of squatting with proper form. This guide covers stance, depth, and breathing techniques for safe and effective squats.'
        ],
        [
            'title' => 'Basic Stretching Routine',
            'thumbnail' => 'https://img.youtube.com/vi/sTxC3J3gQEU/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/sTxC3J3gQEU',
            'duration' => '8:00',
            'difficulty' => 'Beginner',
            'description' => 'Start your workout right with this full-body stretching routine. Perfect for warming up and improving flexibility.'
        ]
    ],
    'intermediate' => [
        [
            'title' => 'Advanced HIIT Workout',
            'thumbnail' => 'https://img.youtube.com/vi/ml6cT4AZdqI/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/ml6cT4AZdqI',
            'duration' => '15:00',
            'difficulty' => 'Intermediate',
            'description' => 'Intense HIIT workout combining bodyweight exercises and cardio intervals for maximum fat burn and conditioning.'
        ],
        [
            'title' => 'Core Strength Training',
            'thumbnail' => 'https://img.youtube.com/vi/DHD1-2P94DI/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/DHD1-2P94DI',
            'duration' => '12:30',
            'difficulty' => 'Intermediate',
            'description' => 'Build a strong core with this comprehensive workout. Includes planks, crunches, and advanced variations.'
        ],
        [
            'title' => 'Full Body Dumbbell Routine',
            'thumbnail' => 'https://img.youtube.com/vi/y-wV4Venusw/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/y-wV4Venusw',
            'duration' => '20:00',
            'difficulty' => 'Intermediate',
            'description' => 'Complete full-body workout using only dumbbells. Perfect for home workouts or gym sessions.'
        ]
    ],
    'advanced' => [
        [
            'title' => 'Olympic Lifting Techniques',
            'thumbnail' => 'https://img.youtube.com/vi/9HyWjAk7fhY/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/9HyWjAk7fhY',
            'duration' => '25:00',
            'difficulty' => 'Advanced',
            'description' => 'Master Olympic lifting with proper form and technique. Covers clean and jerk, snatch, and progressive training methods.'
        ],
        [
            'title' => 'Advanced Calisthenics',
            'thumbnail' => 'https://img.youtube.com/vi/tB3X4TjTIes/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/tB3X4TjTIes',
            'duration' => '18:45',
            'difficulty' => 'Advanced',
            'description' => 'Advanced bodyweight exercises including muscle-ups, handstand push-ups, and front lever progressions.'
        ],
        [
            'title' => 'Power Training Circuit',
            'thumbnail' => 'https://img.youtube.com/vi/413P5BMcvTE/maxresdefault.jpg',
            'video_url' => 'https://www.youtube.com/embed/413P5BMcvTE',
            'duration' => '30:00',
            'difficulty' => 'Advanced',
            'description' => 'High-intensity power training circuit combining plyometrics, strength training, and explosive movements.'
        ]
    ]
];
?>

<style>
.video-page {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.1), rgba(52, 152, 219, 0.1));
    padding: 2rem 0;
    min-height: calc(100vh - 60px);
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.category-section {
    margin-bottom: 3rem;
}

.category-header {
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #f1c40f;
    padding-bottom: 0.5rem;
}

.video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.video-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

.video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.video-thumbnail {
    position: relative;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.video-thumbnail img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-duration {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.video-info {
    padding: 1rem;
}

.video-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.difficulty-badge {
    display: inline-block;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.difficulty-beginner { background: #2ecc71; color: white; }
.difficulty-intermediate { background: #f1c40f; color: black; }
.difficulty-advanced { background: #e74c3c; color: white; }

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(241, 196, 15, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.video-thumbnail:hover .play-button {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
}

/* Video Modal Styles */
.video-modal .modal-content {
    background: #1a1a1a;
    border-radius: 15px;
    overflow: hidden;
}

.video-modal .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
}

.video-modal .modal-title {
    color: white;
    font-size: 1.25rem;
}

.video-modal .modal-body {
    padding: 0;
}

.video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.video-description {
    padding: 1.5rem;
    color: #ecf0f1;
    background: linear-gradient(to bottom, rgba(26, 26, 26, 0.8), #1a1a1a);
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

[data-theme="dark"] .video-card {
    background: #2c3e50;
    color: white;
}

[data-theme="dark"] .video-info {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

<div class="container video-page">
    <div class="page-header">
        <h1>HD Video Guides</h1>
        <p class="lead">Premium workout tutorials with expert guidance</p>
    </div>

    <?php foreach ($video_categories as $category => $videos): ?>
    <div class="category-section">
        <h2 class="category-header text-capitalize"><?php echo $category; ?> Level</h2>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
            <div class="video-card" data-video-url="<?php echo htmlspecialchars($video['video_url']); ?>" 
                 data-video-title="<?php echo htmlspecialchars($video['title']); ?>"
                 data-video-description="<?php echo htmlspecialchars($video['description']); ?>">
                <div class="video-thumbnail">
                    <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                    <div class="play-button">
                        <i class="fas fa-play"></i>
                    </div>
                    <div class="video-duration"><?php echo htmlspecialchars($video['duration']); ?></div>
                </div>
                <div class="video-info">
                    <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <span class="difficulty-badge difficulty-<?php echo strtolower($video['difficulty']); ?>">
                        <?php echo htmlspecialchars($video['difficulty']); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Video Modal -->
<div class="modal fade video-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <iframe src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="video-description"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    const modalTitle = document.querySelector('#videoModalLabel');
    const modalIframe = document.querySelector('.video-container iframe');
    const modalDescription = document.querySelector('.video-description');
    
    document.querySelectorAll('.video-card').forEach(card => {
        card.addEventListener('click', function() {
            const videoUrl = this.dataset.videoUrl;
            const videoTitle = this.dataset.videoTitle;
            const videoDescription = this.dataset.videoDescription;
            
            modalTitle.textContent = videoTitle;
            modalIframe.src = videoUrl;
            modalDescription.textContent = videoDescription;
            videoModal.show();
        });
    });
    
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
        modalIframe.src = '';
    });
});
</script> 