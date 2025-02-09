Steam Review Graph

This project is a web-based tool that visualizes Steam game reviews over time. By entering a Steam AppID, the tool fetches review data and game details from the Steam API, then displays the information as an interactive graph using Chart.js.

Features

Fetch Steam Review Data: Enter any Steam AppID to get historical review data.

Interactive Graph: Displays positive and negative reviews with a "Level Line" to show trends.

Dynamic Visualization: Toggle visibility of positive, negative reviews, and the level line.

Real-time Data: Always fetches the latest review data when the page is reloaded.

Technologies Used

PHP: Handles API requests to Steam and processes the data.

Chart.js: Used for rendering the interactive graph.

HTML & CSS: For structuring and styling the web page.

Installation and Usage

Clone the Repository:

git clone https://github.com/yourusername/steam-review-graph.git

Setup Requirements:

A server with PHP support (e.g., Apache, Nginx).

Ensure cURL is enabled in PHP.

Run the Project:

Place the project folder in your server's root directory.

Access the tool via http://localhost/steam-review-graph/index.php.

Usage:

Enter a valid Steam AppID (e.g., 730 for CS:GO).

Click "Get Reviews" to fetch data.

Review statistics and the interactive graph will be displayed.

Project Structure

├── index.php          # Main PHP file handling API calls and rendering the page
├── reviews.json       # Temporary JSON file to store fetched review data
├── README.md          # This file

API References

Steam Review Histogram: https://store.steampowered.com/appreviewhistogram/{appid}

Steam App Details: https://store.steampowered.com/api/appdetails?appids={appid}

Customization

Graph Styling: Modify CSS within <style> tags in index.php.

Chart Configuration: Adjust Chart.js settings inside the <script> section.

License

This project is open-source and available under the MIT License.

Acknowledgements

Chart.js

Steam Web API
