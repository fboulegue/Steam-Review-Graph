<?php
// Delete previous JSON file if exists
$jsonFile = 'reviews.json';
if (file_exists($jsonFile)) {
    unlink($jsonFile);
}

// Fetch new JSON data if AppID is provided
if (isset($_POST['appid'])) {
    $appid = $_POST['appid'];
    $reviewUrl = "https://store.steampowered.com/appreviewhistogram/$appid";
    $appDetailsUrl = "https://store.steampowered.com/api/appdetails?appids=$appid";

    // Fetch review data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $reviewUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $reviewResult = curl_exec($ch);

    // Fetch game details
    curl_setopt($ch, CURLOPT_URL, $appDetailsUrl);
    $appDetailsResult = curl_exec($ch);
    curl_close($ch);

    if ($reviewResult && $appDetailsResult) {
        $reviewData = json_decode($reviewResult, true);
        $appDetails = json_decode($appDetailsResult, true);

        $gameName = $appDetails[$appid]['data']['name'] ?? 'Unknown';
        $positiveReviews = array_sum(array_column($reviewData['results']['rollups'], 'recommendations_up'));
        $negativeReviews = array_sum(array_column($reviewData['results']['rollups'], 'recommendations_down'));
        $totalReviews = $positiveReviews + $negativeReviews;

        $reviewData['game_details'] = [
            'name' => $gameName,
            'positive_reviews' => $positiveReviews,
            'negative_reviews' => $negativeReviews,
            'total_reviews' => $totalReviews
        ];

        file_put_contents($jsonFile, json_encode($reviewData));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steam Review Graph</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            padding: 20px;
            text-align: center;
        }
        #container {
            max-width: 900px;
            margin: auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }
        input[type="text"] {
            width: 70%;
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: #e0e0e0;
        }
        button {
            padding: 12px 25px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #388e3c;
        }
        canvas {
            margin-top: 20px;
        }
        #gameInfo {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div id="container">
        <h1>Steam Review Graph Generator</h1>
        <form method="POST">
            <input type="text" name="appid" placeholder="Enter Steam AppID" required>
            <button type="submit">Get Reviews</button>
        </form>
        <div id="gameInfo"></div>
        <canvas id="reviewChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        <?php if (file_exists($jsonFile)) : ?>
            const data = <?php echo file_get_contents($jsonFile); ?>;

            const dates = data.results.rollups.map(item => new Date(item.date * 1000).toLocaleDateString());
            const positive = data.results.rollups.map(item => item.recommendations_up);
            const negative = data.results.rollups.map(item => -item.recommendations_down);

            const levelLineData = positive.map((pos, idx) => (pos + negative[idx]) / 2);

            const maxY = Math.max(...positive);
            const minY = Math.min(...negative);

            const gameDetails = data.game_details;

            document.getElementById('gameInfo').innerHTML = `
                <h2>${gameDetails.name}</h2>
                <p><strong>Total Reviews:</strong> ${gameDetails.total_reviews.toLocaleString()}</p>
                <p><strong>Positive Reviews:</strong> ${gameDetails.positive_reviews.toLocaleString()}</p>
                <p><strong>Negative Reviews:</strong> ${gameDetails.negative_reviews.toLocaleString()}</p>
            `;

            const ctx = document.getElementById('reviewChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Negative Reviews',
                            data: negative,
                            borderColor: '#f44336',
                            backgroundColor: 'rgba(244, 67, 54, 0.2)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            hidden: false
                        },
                        {
                            label: 'Positive Reviews',
                            data: positive,
                            borderColor: '#4caf50',
                            backgroundColor: 'rgba(76, 175, 80, 0.2)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            hidden: false
                        },
                        {
                            label: 'Level Line',
                            data: levelLineData,
                            borderColor: 'purple',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            pointRadius: 0,
                            hidden: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e0e0e0'
                            },
                            onClick: function (e, legendItem, legend) {
                                const index = legendItem.datasetIndex;
                                const ci = legend.chart;
                                ci.data.datasets[index].hidden = !ci.data.datasets[index].hidden;
                                ci.update();
                            }
                        },
                        tooltip: {
                            backgroundColor: '#333',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#555',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#e0e0e0'
                            },
                            grid: {
                                color: '#444'
                            },
                            title: {
                                display: true,
                                text: 'Date',
                                color: '#e0e0e0'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#e0e0e0'
                            },
                            grid: {
                                drawBorder: false,
                                color: function(context) {
                                    if (context.tick.value === 0) {
                                        return 'rgba(255, 255, 255, 0.8)'; // White dashed line at 0
                                    }
                                    return context.tick.value > 0 ? '#666' : '#333';
                                },
                                borderDash: function(context) {
                                    return context.tick.value === 0 ? [5, 5] : [];
                                }
                            },
                            title: {
                                display: true,
                                text: 'Number of Reviews',
                                color: '#e0e0e0'
                            },
                            min: minY,
                            max: maxY
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>
