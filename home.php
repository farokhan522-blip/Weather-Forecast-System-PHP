<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Weather Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <style>
        body {
            background: url('home.jpg') no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: brightness(0.8);
            min-height: 100vh;
            color: white;
        }

        .navbar-custom {
            background: linear-gradient(
            to  right,
            #0f2027,      /* deep stormy night */
            #203a43,      /* dark dusk sky */
            #4facfe,      /* soft sky blue */
            #f9d976,      /* mild yellow sunlight (reduced) */
            #ffffff       /* snowy white */
        );
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }

        .logo-text {
            color: #f8f9fa; /* brighter for contrast */
            font-weight: bold;
            font-size: 18px;
            white-space: nowrap;
        }

        .emoji-logo {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            width: 48px;
            height: 48px;
            border: 2px solid white;
            background-color: rgba(255, 255, 255, 0.1);
            box-sizing: content-box;
        }

        .emoji-logo div {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .emoji-logo div:nth-child(1),
        .emoji-logo div:nth-child(2) {
            border-bottom: 1px solid white;
        }

        .emoji-logo div:nth-child(1),
        .emoji-logo div:nth-child(3) {
            border-right: 1px solid white;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-text {
            color: white;
            font-weight: bold;
            font-size: 18px;
            white-space: nowrap;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background-color: #009688;
            border: none;
        }

        .btn-primary:hover {
            background-color: #00796b;
        }

        .btn-secondary {
            background-color: #757575;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5e5e5e;
        }

        .navbar .btn {
            white-space: nowrap;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.2); /* ✅ light white with 20% opacity */
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            color:white;
        }

        h1, h2, h3, p, a, span {
            color: #ffffff; /* clean white text */
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7); /* soft black glow behind text */
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid d-flex justify-content-between align-items-center px-3">
        <div class="logo-box">
            <div class="emoji-logo">
                <div>☀️</div>
                <div>🌧️</div>
                <div>❄️</div>
                <div>🌤️</div>
            </div>
            <span class="logo-text">Weather Dash ⚡</span>
        </div>

        <div class="d-flex gap-2">
            <?php if ($role === 'admin' || $role === 'super_admin') : ?>
                <a class="btn btn-light btn-sm" href="manageusers.php">👥 See Users</a>
            <?php endif; ?>
            <a class="btn btn-danger btn-sm" href="logout.php">🚪 Logout</a>
        </div>
    </div>
</nav>

<!-- Body -->
<div class="container mt-5">
    

    <h3  class="fw-bold" style = color:white;>Welcome, <?= $_SESSION['name'] ?>!</h3>
    <p style = color:white;>This is your personalized weather dashboard. Below is your current forecast. You can also view more options.</p>

    <!-- Real-time current forecast -->
    <div id="forecastResult" class="mt-4 border p-3 rounded card-glass"></div>

    <!-- Buttons to show detailed forecast -->
    <div class="my-4">
        <button class="btn btn-primary btn-sm me-2" onclick="getForecast('hourly')">📅 Today's Hourly Forecast</button>
        <button class="btn btn-secondary btn-sm" onclick="getForecast('daily')">📆 5-Day Forecast</button>
    </div>

    <!-- Forecast output area -->
    <div id="customForecast" class="mt-4 border p-3 rounded card-glass"></div>
</div>

<!-- JS Scripts -->
<script>
const apiKey = "6b0b73af51561345267069d90c0346a7";

// Show current weather and save to DB
window.onload = function () {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`)
                .then(response => response.json())
                .then(data => {
                    const forecast = data.weather[0].description;
                    const city = data.name;
                    const country = data.sys.country;

                    document.getElementById("forecastResult").innerHTML = `
                        <p><strong>Location:</strong> ${city}, ${country}</p>
                        <p><strong>Weather:</strong> ${forecast}</p>
                    `;

                    // Send to DB
                    fetch('save_forecast.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: lat,
                            longitude: lon,
                            city: city,
                            country: country,
                            forecast: forecast
                        })
                    })
                    .then(res => res.text())
                    .then(responseText => {
                        console.log("🔁 Response from PHP:", responseText);
                    })
                    .catch(err => console.error("❌ Save error:", err));
                });
        }, () => {
            document.getElementById("forecastResult").innerText = "Location permission denied.";
        });
    }
};

// Emoji mapper
function getWeatherEmoji(condition) {
    switch(condition.toLowerCase()) {
        case "clear": return "☀️ Sunny";
        case "clouds": return "☁️ Cloudy";
        case "rain": return "🌧️ Rainy";
        case "snow": return "❄️ Snowy";
        case "thunderstorm": return "🌩️ Stormy";
        case "mist":
        case "fog": return "🌫️ Foggy";
        default: return condition;
    }
}

// Hourly & Daily forecast
function getForecast(type) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            const url = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let html = "";

                    if (type === "hourly") {
                        html += "<h5>Today's Hourly Forecast:</h5>";
                        const today = new Date().toISOString().split("T")[0];
                        const filtered = data.list.filter(f => f.dt_txt.startsWith(today));
                        filtered.forEach(f => {
                            html += `<p><strong>${f.dt_txt.split(" ")[1]}</strong> - ${f.main.temp}°C, ${f.weather[0].description}</p>`;
                        });
                    } else {
                        html += "<h5>5-Day Forecast:</h5>";
                        const days = {};

                        data.list.forEach(f => {
                            const date = f.dt_txt.split(" ")[0];
                            if (!days[date]) days[date] = [];
                            days[date].push({
                                temp: f.main.temp,
                                condition: f.weather[0].main
                            });
                        });

                        for (const date in days) {
                            const temps = days[date].map(e => e.temp);
                            const avgTemp = (temps.reduce((a, b) => a + b, 0) / temps.length).toFixed(1);

                            const conditions = days[date].map(e => e.condition);
                            const freq = {};
                            conditions.forEach(c => freq[c] = (freq[c] || 0) + 1);
                            const mostCommon = Object.keys(freq).reduce((a, b) => freq[a] > freq[b] ? a : b);
                            const summary = getWeatherEmoji(mostCommon);

                            html += `<p><strong>${date}:</strong> Avg Temp: ${avgTemp}°C — ${summary}</p>`;
                        }
                    }

                    document.getElementById("customForecast").innerHTML = html;
                });
        });
    }
}
</script>

</body>
</html>