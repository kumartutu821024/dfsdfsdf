<?php
header('Content-Type: text/html; charset=utf-8');

// Get course ID and folder ID from query parameters
$courseId = isset($_GET['id']) ? str_replace('nexttoppers_', '', $_GET['id']) : null;
$folderId = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;

if (!$courseId) {
    die('<div style="text-align: center; padding: 40px; font-family: Arial; color: #666;">Invalid course ID</div>');
}

// Build API URL based on whether we're viewing a folder or course
if ($folderId) {
    $apiUrl = "https://api.thescholarverse.site/nexttoppers/course/all-content/{$courseId}?limit=1000&folder_id={$folderId}";
} else {
    $apiUrl = "https://api.thescholarverse.site/nexttoppers/course/all-content/{$courseId}?limit=1000";
}

// Fetch content from API
$response = @file_get_contents($apiUrl);

if ($response === false) {
    die('<div style="text-align: center; padding: 40px; font-family: Arial; color: #d32f2f;">Failed to load course content. Please try again.</div>');
}

$data = json_decode($response, true);

if (!$data || !isset($data['data'])) {
    die('<div style="text-align: center; padding: 40px; font-family: Arial; color: #d32f2f;">No content found for this course.</div>');
}

$content = $data['data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Content</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            min-height: 100vh;
        }

        /* Loading Bar */
        .loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            width: 0%;
            z-index: 9999;
            animation: none;
            transition: width 0.3s ease;
        }

        .loading-bar.active {
            animation: loadingBar 1.5s ease-in-out infinite;
        }

        @keyframes loadingBar {
            0% { width: 10%; }
            50% { width: 80%; }
            100% { width: 100%; visibility: hidden; }
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9998;
        }

        .loading-spinner.active {
            display: block;
        }

        .spinner {
            border: 4px solid rgba(102, 126, 234, 0.1);
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            border-bottom: 2px solid #e5e7eb;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 16px;
            backdrop-filter: blur(10px);
        }

        .header-content {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .header-title {
            flex: 1;
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Main Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 16px 48px;
        }

        /* Tab Navigation */
        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0;
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .tab {
            padding: 12px 16px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin: -12px 0 -3px 0;
            border-radius: 8px 8px 0 0;
            position: relative;
        }

        .tab:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .tab.active {
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* Content Items List */
        .content-items {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .content-item-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: white;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .content-item-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
            border-color: #667eea;
        }

        .content-item-icon {
            width: 60px;
            height: 60px;
            min-width: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .content-item-icon.folder {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .content-item-icon.video {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            position: relative;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .content-item-icon.video::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 10px solid white;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            left: 50%;
            top: 50%;
            transform: translate(-40%, -50%);
        }

        .content-item-info {
            flex: 1;
        }

        .content-item-title {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .content-item-desc {
            font-size: 13px;
            color: #6b7280;
        }

        .content-item-arrow {
            font-size: 18px;
            color: #d1d5db;
            transition: all 0.3s;
        }

        .content-item-card:hover .content-item-arrow {
            color: #667eea;
            transform: translateX(4px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header-title {
                font-size: 14px;
            }

            .content-item-card {
                padding: 12px;
                gap: 12px;
            }

            .content-item-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .content-item-title {
                font-size: 14px;
            }

            .tabs {
                gap: 12px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .tab {
                padding: 12px 0;
                min-width: max-content;
            }
        }
    </style>
</head>
<body>

<!-- Loading Bar -->
<div class="loading-bar" id="loadingBar"></div>

<!-- Loading Spinner -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner"></div>
</div>

<!-- Header -->
<div class="header">
    <div class="header-content">
        <button class="back-btn" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <div class="header-title">🎓 Course Content</div>
    </div>
</div>

<!-- Main Container -->
<div class="container">
    <!-- Main Tab Navigation -->
    <div class="tabs">
        <button class="tab active" onclick="switchTab('live')">🔴 Live</button>
        <button class="tab" onclick="switchTab('content')">📚 Content</button>
        <button class="tab" onclick="switchTab('lectures')">🎥 Demo Lectures</button>
    </div>

    <!-- Live Tab -->
    <div id="liveTab" style="display: block;">
        <!-- Live Sub-tabs -->
        <div class="tabs" style="margin-bottom: 20px; border-bottom: 1px solid #e5e7eb;">
            <button class="tab active" onclick="switchLiveTab('upcoming')">📅 Upcoming</button>
            <button class="tab" onclick="switchLiveTab('live')">🔴 Live</button>
            <button class="tab" onclick="switchLiveTab('completed')">✅ Completed</button>
        </div>
        <div id="upcomingTab" style="display: block;">
            <div class="content-items" id="upcomingList"></div>
        </div>
        <div id="liveSubTab" style="display: none;">
            <div class="content-items" id="liveList"></div>
        </div>
        <div id="completedTab" style="display: none;">
            <div class="content-items" id="completedList"></div>
        </div>
    </div>

    <!-- Content Tab -->
    <div id="contentTab" style="display: none;">
        <div class="content-items" id="contentList"></div>
    </div>

    <!-- Lectures Tab -->
    <div id="lecturesTab" style="display: none;">
        <div class="empty-state">
            <i class="fas fa-video"></i>
            <h3>Demo Lectures</h3>
            <p>No demo lectures available for this course yet.</p>
        </div>
    </div>
</div>

<!-- Content Detail Modal -->
<div id="contentDetailModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; max-width: 800px; margin: 40px auto; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h2 id="modalTitle" style="font-size: 18px; font-weight: 700; color: #1f2937;"></h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">×</button>
        </div>
        <div id="modalContent" style="padding: 24px; max-height: 600px; overflow-y: auto;"></div>
        <div style="padding: 24px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; justify-content: flex-end;">
            <button onclick="closeModal()" style="padding: 10px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-weight: 600; color: #374151;">Close</button>
        </div>
    </div>
</div>

<script>
    // Content data from API
    const allContent = <?php echo json_encode($content); ?>;
    const courseId = '<?php echo $courseId; ?>';
    const currentFolderId = <?php echo $folderId ? "'{$folderId}'" : 'null'; ?>;

    // Navigation history for breadcrumb
    let navigationHistory = [];

    // Loading bar functions
    function showLoading() {
        const bar = document.getElementById('loadingBar');
        const spinner = document.getElementById('loadingSpinner');
        bar.classList.add('active');
        spinner.classList.add('active');
    }

    function hideLoading() {
        const bar = document.getElementById('loadingBar');
        const spinner = document.getElementById('loadingSpinner');
        bar.classList.remove('active');
        spinner.classList.remove('active');
    }

    // Tab switching for main tabs
    function switchTab(tab) {
        // Find the main tabs container (first .tabs div)
        const mainTabsContainer = document.querySelector('.container > .tabs');
        const mainTabs = mainTabsContainer.querySelectorAll('.tab');
        mainTabs.forEach(t => t.classList.remove('active'));

        // Show/hide main tab content
        document.getElementById('liveTab').style.display = 'none';
        document.getElementById('contentTab').style.display = 'none';
        document.getElementById('lecturesTab').style.display = 'none';

        // Find which button was clicked and activate it
        const clickedBtn = event.target.closest('.tab');
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }

        document.getElementById(tab + 'Tab').style.display = 'block';

        // Load content if first time accessing
        if (tab === 'live' && !window.liveClassesLoaded) {
            loadLiveClasses('upcoming');
            window.liveClassesLoaded = true;
        } else if (tab === 'content' && !window.contentLoaded) {
            initContent();
            window.contentLoaded = true;
        }
    }

    // Tab switching for live sub-tabs
    function switchLiveTab(type) {
        // Find the live sub-tabs container (second .tabs div inside #liveTab)
        const liveTabsContainer = document.querySelector('#liveTab > .tabs');
        const liveTabs = liveTabsContainer.querySelectorAll('.tab');
        liveTabs.forEach(t => t.classList.remove('active'));

        // Show/hide live sub-tab content
        document.getElementById('upcomingTab').style.display = 'none';
        document.getElementById('liveSubTab').style.display = 'none';
        document.getElementById('completedTab').style.display = 'none';

        // Find which button was clicked and activate it
        const clickedBtn = event.target.closest('.tab');
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }

        if (type === 'upcoming') {
            document.getElementById('upcomingTab').style.display = 'block';
        } else if (type === 'live') {
            document.getElementById('liveSubTab').style.display = 'block';
        } else if (type === 'completed') {
            document.getElementById('completedTab').style.display = 'block';
        }

        loadLiveClasses(type);
    }

    // Fetch and display live classes
    async function loadLiveClasses(type) {
        const apiUrl = `https://api.thescholarverse.site/nexttoppers/course/classes?type=${type}&id=${courseId}`;
        const containerId = type === 'upcoming' ? 'upcomingList' : (type === 'live' ? 'liveList' : 'completedList');

        showLoading();

        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                const html = data.data.map((cls, index) => {
                    const title = cls.title || cls.name || `Class ${index + 1}`;
                    const desc = cls.description || cls.data?.description || 'Live Class';
                    const time = cls.start_time || cls.data?.start_time || '';
                    const recordingUrl = cls.recording_url || cls.data?.recording_url || '';
                    const videoId = cls.id || cls.entity_id || cls.video_id || cls.data?.id || '';

                    // For live classes, show a join/watch button or description
                    let action = '';
                    if (type === 'live') {
                        // Live class - open Nexttoppers player
                        const playerUrl = videoId ? `https://nexttoppers.com/play/${videoId}-${courseId}` : '#';
                        action = `<a href="${playerUrl}" target="_blank" style="margin-top: 8px; display: inline-block; padding: 8px 16px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);" onmouseover="this.style.boxShadow='0 8px 20px rgba(239, 68, 68, 0.4)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.3)'; this.style.transform='translateY(0)';">▶ Watch Live</a>`;
                    } else if (type === 'completed' && recordingUrl) {
                        action = `<a href="${recordingUrl}" target="_blank" style="margin-top: 8px; display: inline-block; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);" onmouseover="this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.3)'; this.style.transform='translateY(0)';">📹 Watch Recording</a>`;
                    } else if (type === 'upcoming' && time) {
                        action = `<span style="margin-top: 8px; display: inline-block; padding: 8px 16px; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 8px; font-size: 12px; font-weight: 600;">⏰ ${time}</span>`;
                    }

                    return `
                        <div class="content-item-card" style="cursor: default; flex-direction: column; align-items: flex-start; background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, white 100%); border: 1px solid rgba(102, 126, 234, 0.1);">
                            <div style="width: 100%;">
                                <div class="content-item-title" style="color: #1f2937; margin-bottom: 6px;">${title}</div>
                                <div class="content-item-desc" style="color: #6b7280; line-height: 1.5;">${desc}</div>
                            </div>
                            ${action}
                        </div>
                    `;
                }).join('');

                document.getElementById(containerId).innerHTML = html;
            } else {
                document.getElementById(containerId).innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No ${type} classes</h3>
                        <p>There are no ${type} classes available.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error(`Error loading ${type} classes:`, error);
            document.getElementById(containerId).innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error loading ${type} classes</h3>
                    <p>${error.message}</p>
                </div>
            `;
        } finally {
            hideLoading();
        }
    }

    // Modal functions
    function openModal(title, content) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalContent').innerHTML = `
            <div style="font-size: 14px; line-height: 1.6; color: #374151;">
                ${content || '<p style="color: #6b7280;">No description available</p>'}
            </div>
        `;
        document.getElementById('contentDetailModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('contentDetailModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Navigate into folder
    function openFolder(folderId, folderName) {
        navigationHistory.push(currentFolderId);
        const url = new URL(window.location);
        url.searchParams.set('folder_id', folderId);
        window.location.href = url.toString();
    }

    // Go back to parent folder
    function goBack() {
        if (currentFolderId) {
            // If we're in a folder, go back to parent (remove folder_id)
            const url = new URL(window.location);
            url.searchParams.delete('folder_id');
            window.location.href = url.toString();
        } else {
            // If we're at root, go back to home
            window.history.back();
        }
    }

    // Get icon and type for content
    function getContentType(item) {
        // API response has type: "folder" or "file"
        if (item.type === 'folder') {
            return { icon: '📁', type: 'folder' };
        }
        if (item.type === 'file') {
            return { icon: '📄', type: 'document' };
        }
        return { icon: '📄', type: 'document' };
    }

    // Initialize content display
    function initContent() {
        if (!allContent || allContent.length === 0) {
            document.getElementById('contentList').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Content Available</h3>
                    <p>This folder doesn't have any content yet.</p>
                </div>
            `;
            return;
        }

        console.log('Loading content:', allContent);
        let html = '';

        // Process all items
        allContent.forEach((item, index) => {
            const { icon, type } = getContentType(item);
            // Use title field from API response
            const title = item.title || item.name || `Item ${index + 1}`;
            // If API is video-only it may not set type; detect video by common id fields
            const videoId = item.entity_id || item.id || item.data?.id || item.data?.video_id || null;
            // Get description from data object or use folder info
            const desc = item.data?.content_counts ?
                `${Object.values(item.data.content_counts).reduce((a, b) => {
                    if (typeof b === 'object') return a + (b.free || 0) + (b.paid || 0);
                    return a;
                }, 0)} items` :
                (type === 'folder' ? 'Folder' : 'Content');

            const itemClass = type === 'folder' ? 'folder' : type === 'video' ? 'video' : '';

            if (type === 'folder' && item.entity_id) {
                // Folder is clickable and navigates to folder content
                html += `
                    <div class="content-item-card" onclick="openFolder(${item.entity_id}, '${title.replace(/'/g, "\\'")}');" style="cursor: pointer; background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(249, 115, 22, 0.05) 100%); border: 1px solid rgba(249, 115, 22, 0.2);">
                        <div class="content-item-icon folder" style="box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);">
                            📁
                        </div>
                        <div class="content-item-info">
                            <div class="content-item-title">${title}</div>
                            <div class="content-item-desc">${desc}</div>
                        </div>
                        <div class="content-item-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            } else if (videoId) {
                // If this item looks like a video, clicking should open Nexttoppers player
                // Use courseId from page as batch/course id
                const playerUrl = `https://nexttoppers.com/play/${videoId}-${courseId}`;
                html += `
                    <div class="content-item-card" onclick="window.location.href='${playerUrl}';" style="cursor: pointer; background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%); border: 1px solid rgba(239, 68, 68, 0.2);">
                        <div class="content-item-icon video" style="box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                            🎬
                        </div>
                        <div class="content-item-info">
                            <div class="content-item-title">${title}</div>
                            <div class="content-item-desc">${desc}</div>
                        </div>
                        <div class="content-item-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            } else {
                // Regular content opens in modal
                const modalContent = item.data?.description || item.data?.file_url || item.title || 'No details available';
                html += `
                    <div class="content-item-card" onclick="openModal('${title.replace(/'/g, "\\'")}', '${modalContent.replace(/'/g, "\\'")}');" style="cursor: pointer; background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); border: 1px solid rgba(102, 126, 234, 0.2);">
                        <div class="content-item-icon" style="box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                            📄
                        </div>
                        <div class="content-item-info">
                            <div class="content-item-title">${title}</div>
                            <div class="content-item-desc">${desc}</div>
                        </div>
                        <div class="content-item-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;
            }
        });

        document.getElementById('contentList').innerHTML = html || '<div class="empty-state"><i class="fas fa-inbox"></i><h3>No Content</h3></div>';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('contentDetailModal');
        if (e.target === modal) {
            closeModal();
        }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        // If viewing a folder, load Content tab; otherwise load Live tab
        if (currentFolderId) {
            // We're in a folder view, load content tab
            initContent();
            window.contentLoaded = true;
            // Make sure content tab is active
            const mainTabsContainer = document.querySelector('.container > .tabs');
            const mainTabs = mainTabsContainer.querySelectorAll('.tab');
            mainTabs.forEach(t => t.classList.remove('active'));
            mainTabs[1].classList.add('active'); // Content tab is second (index 1)
            document.getElementById('liveTab').style.display = 'none';
            document.getElementById('contentTab').style.display = 'block';
            document.getElementById('lecturesTab').style.display = 'none';
        } else {
            // Load upcoming classes by default
            loadLiveClasses('upcoming');
            window.liveClassesLoaded = true;
        }
    });
</script>

</body>
</html>

