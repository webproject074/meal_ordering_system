<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_CANTEEN]);

// Handle form submission for adding a new menu item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $category = $_POST['category'] ?? '';
    $imageFilename = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/menu_images/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageFilename = 'menuimg_' . uniqid() . '.' . strtolower($ext);
        $targetPath = $uploadDir . $imageFilename;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            setMessage('Image upload failed', 'error');
            redirect(BASE_URL . '/canteen/manage_menu.php');
        }
    }

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $category = $_POST['category'] ?? '';
    
    // Validate input
    if (empty($name) || empty($description) || $price <= 0 || empty($category)) {
        setMessage('All fields are required and price must be greater than 0', 'error');
    } else {
        // Create new menu item
        $menuItems = readJsonFile(MENUS_FILE);
        
        $newMenuItem = [
            'id' => 'menu' . generateUniqueId(),
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'availability' => true,
            'image' => $imageFilename
        ];
        
        $menuItems[] = $newMenuItem;
        
        if (writeJsonFile(MENUS_FILE, $menuItems)) {
            setMessage('Menu item added successfully', 'success');
        } else {
            setMessage('Failed to add menu item', 'error');
        }
    }
    
    // Redirect to prevent form resubmission
    redirect(BASE_URL . '/canteen/manage_menu.php');
}

// Handle form submission for updating a menu item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $imageFilename = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/menu_images/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageFilename = 'menuimg_' . uniqid() . '.' . strtolower($ext);
        $targetPath = $uploadDir . $imageFilename;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            setMessage('Image upload failed', 'error');
            redirect(BASE_URL . '/canteen/manage_menu.php');
        }
    }

    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $category = $_POST['category'] ?? '';
    $availability = isset($_POST['availability']) ? true : false;
    
    // Validate input
    if (empty($id) || empty($name) || empty($description) || $price <= 0 || empty($category)) {
        setMessage('All fields are required and price must be greater than 0', 'error');
    } else {
        // Update menu item
        $menuItems = readJsonFile(MENUS_FILE);
        
        $updated = false;
        foreach ($menuItems as &$item) {
            if ($item['id'] === $id) {
                $item['name'] = $name;
                $item['description'] = $description;
                $item['price'] = $price;
                $item['category'] = $category;
                $item['availability'] = $availability;
                if ($imageFilename !== '') {
                    $item['image'] = $imageFilename;
                }
                $updated = true;
                break;
            }
        }
        
        if ($updated && writeJsonFile(MENUS_FILE, $menuItems)) {
            setMessage('Menu item updated successfully', 'success');
        } else {
            setMessage('Failed to update menu item', 'error');
        }
    }
    
    // Redirect to prevent form resubmission
    redirect(BASE_URL . '/canteen/manage_menu.php');
}

// Handle menu item deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Delete menu item
    $menuItems = readJsonFile(MENUS_FILE);
    
    $deleted = false;
    foreach ($menuItems as $index => $item) {
        if ($item['id'] === $id) {
            array_splice($menuItems, $index, 1);
            $deleted = true;
            break;
        }
    }
    
    if ($deleted && writeJsonFile(MENUS_FILE, $menuItems)) {
        setMessage('Menu item deleted successfully', 'success');
    } else {
        setMessage('Failed to delete menu item', 'error');
    }
    
    // Redirect
    redirect(BASE_URL . '/canteen/manage_menu.php');
}

// Handle toggling menu item availability
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    
    // Toggle menu item availability
    $menuItems = readJsonFile(MENUS_FILE);
    
    $toggled = false;
    foreach ($menuItems as &$item) {
        if ($item['id'] === $id) {
            $item['availability'] = !$item['availability'];
            $toggled = true;
            break;
        }
    }
    
    if ($toggled && writeJsonFile(MENUS_FILE, $menuItems)) {
        $status = $item['availability'] ? 'available' : 'unavailable';
        setMessage("Menu item marked as $status", 'success');
    } else {
        setMessage('Failed to update menu item availability', 'error');
    }
    
    // Redirect
    redirect(BASE_URL . '/canteen/manage_menu.php');
}

// Get all menu items
$menuItems = readJsonFile(MENUS_FILE);

// Group menu items by category
$menuByCategory = [];
foreach ($menuItems as $item) {
    $category = $item['category'];
    if (!isset($menuByCategory[$category])) {
        $menuByCategory[$category] = [];
    }
    $menuByCategory[$category][] = $item;
}

// Get message
$message = getMessage();

// Get menu item to edit if provided
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($menuItems as $item) {
        if ($item['id'] === $editId) {
            $editItem = $item;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - <?php echo SITE_NAME; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include '../includes/canteen_sidebar.php'; ?>
        
        <div class="md:ml-64 w-full p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Manage Menu</h1>
                <a href="<?php echo BASE_URL; ?>/canteen/dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Back to Dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6"><?php echo $editItem ? 'Edit Menu Item' : 'Add New Menu Item'; ?></h2>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'add'; ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $editItem ? $editItem['name'] : ''; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" required 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 h-24"><?php echo $editItem ? $editItem['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo $editItem ? $editItem['price'] : ''; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="category" name="category" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Category</option>
                            <option value="Morning Snack" <?php echo ($editItem && $editItem['category'] === 'Morning Snack') ? 'selected' : ''; ?>>Morning Snack</option>
                            <option value="Lunch" <?php echo ($editItem && $editItem['category'] === 'Lunch') ? 'selected' : ''; ?>>Lunch</option>
                            <option value="Dinner" <?php echo ($editItem && $editItem['category'] === 'Dinner') ? 'selected' : ''; ?>>Dinner</option>
                        </select>
                    </div>
                    
                    <?php if ($editItem): ?>
                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="availability" <?php echo $editItem['availability'] ? 'checked' : ''; ?> 
                                       class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Available</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        <?php if ($editItem && !empty($editItem['image'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo BASE_URL . '/assets/menu_images/' . htmlspecialchars($editItem['image']); ?>" alt="Menu Image" class="h-20 rounded shadow">
                                <p class="text-xs text-gray-500 mt-1">Current image</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-wrap gap-3 mt-6">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                            <?php echo $editItem ? 'Update Menu Item' : 'Add Menu Item'; ?>
                        </button>
                        <?php if ($editItem): ?>
                            <a href="<?php echo BASE_URL; ?>/canteen/manage_menu.php" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Current Menu Items</h2>
                
                <?php if (empty($menuItems)): ?>
                    <p class="text-gray-600">No menu items found. Add your first menu item using the form above.</p>
                <?php else: ?>
                    <?php foreach ($menuByCategory as $category => $items): ?>
                        <h3 class="text-lg font-medium text-gray-800 mt-6 mb-3 pb-2 border-b border-gray-200"><?php echo $category; ?></h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($items as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo BASE_URL . '/assets/menu_images/' . htmlspecialchars($item['image']); ?>" alt="Menu Image" class="h-12 w-12 object-cover rounded">
                                                <?php else: ?>
                                                    <span class="text-gray-400 text-xs">No Image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['name']; ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?php echo $item['description']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($item['price']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $item['availability'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo $item['availability'] ? 'Available' : 'Unavailable'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="<?php echo BASE_URL; ?>/canteen/manage_menu.php?edit=<?php echo $item['id']; ?>" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>
                                                <a href="<?php echo BASE_URL; ?>/canteen/manage_menu.php?toggle=<?php echo $item['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                    <?php echo $item['availability'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/canteen/manage_menu.php?delete=<?php echo $item['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this menu item?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
