<?php
// Function to load products from the API
function loadProducts($apiUrl) {
    $json = file_get_contents($apiUrl);  // Fetch data from the API
    return json_decode($json, true);  // Decode JSON data to an associative array
}

// Fetch the products from the fakestoreapi
$apiUrl = 'https://fakestoreapi.com/products';
$products = loadProducts($apiUrl);

// Function to get all unique categories
function getAllCategories($products) {
    $categories = [];
    foreach ($products as $product) {
        if (!in_array($product['category'], $categories)) {
            $categories[] = $product['category'];
        }
    }
    return $categories;
}

// Handle form submission for filtering
$filteredProducts = $products;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $minPrice = $_POST['minPrice'] ?? 0;
    $maxPrice = $_POST['maxPrice'] ?? 99999;
    $sortByPrice = $_POST['sortByPrice'] ?? 'asc';

    // Apply filtering based on category and price range
    $filteredProducts = array_filter($products, function ($product) use ($category, $minPrice, $maxPrice) {
        $categoryMatch = !$category || $product['category'] == $category;
        $priceMatch = $product['price'] >= $minPrice && $product['price'] <= $maxPrice;
        return $categoryMatch && $priceMatch;
    });

    // Sort the filtered products by price
    usort($filteredProducts, function ($a, $b) use ($sortByPrice) {
        if ($sortByPrice === 'asc') {
            return $a['price'] - $b['price'];
        } else {
            return $b['price'] - $a['price'];
        }
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Filter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Product Filter</h1>

        <!-- Filter Form -->
        <form action="" method="POST">
            <div class="filter-form">
                <!-- Category Filter -->
                <div class="form-item">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="">--Select Category--</option>
                        <?php
                            // Loop through categories and create options dynamically
                            foreach (getAllCategories($products) as $category) {
                                echo "<option value=\"$category\">$category</option>";
                            }
                        ?>
                    </select>
                </div>

                <!-- Price Filter -->
                <div class="form-item">
                    <label for="minPrice">Price:</label>
                    <input type="number" id="minPrice" name="minPrice"  placeholder="min" />
                    <input type="number" id="maxPrice" name="maxPrice"  placeholder="max" />
                </div>
                <!-- <div class="form-item">
                    <label for="maxPrice"></label>
                </div> -->

                <!-- Sort by Price -->
                <div class="form-item">
                    <label for="sortByPrice">Sort by:</label>
                    <select name="sortByPrice" id="sortByPrice">
                        <option value="asc">Price: Low to High</option>
                        <option value="desc">Price: High to Low</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="form-item">
                    <input type="submit" value="Filter">
                </div>
            </div>
        </form>

        <!-- Product Cards -->
        <div class="product-container">
            <?php
                // Dynamically display products after filtering
                foreach ($filteredProducts as $product) {
                    echo '<div class="product-card">';
                    echo '<img src="' . $product['image'] . '" alt="' . $product['title'] . '">';
                    echo '<h3>' . $product['title'] . '</h3>';
                    echo '<p>Category: ' . $product['category'] . '</p>';
                    echo '<p class="price">€' . number_format($product['price'], 2) . '</p>';
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</body>
</html>
