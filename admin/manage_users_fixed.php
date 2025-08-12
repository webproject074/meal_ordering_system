<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Initialize variables for edit mode
$editMode = false;
$editUser = null;

// Handle form submission for adding or editing a user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $department_name = $_POST['department_name'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    
    // Validate input
    if (empty($username) || (empty($password) && empty($userId)) || empty($role)) {
        setMessage('Username and role are required' . (empty($userId) ? ', and password is required for new users' : ''), 'error');
    } else {
        // Get all users
        $users = readJsonFile(USERS_FILE);
        
        // Editing existing user
        if (!empty($userId)) {
            $userIndex = -1;
            foreach ($users as $index => $user) {
                if ($user['id'] === $userId) {
                    $userIndex = $index;
                    break;
                }
            }
            
            if ($userIndex !== -1) {
                // Check if username already exists and belongs to another user
                $usernameExists = false;
                foreach ($users as $user) {
                    if ($user['username'] === $username && $user['id'] !== $userId) {
                        $usernameExists = true;
                        break;
                    }
                }
                
                if ($usernameExists) {
                    setMessage('Username already exists', 'error');
                } else {
                    // Update user
                    $users[$userIndex]['username'] = $username;
                    $users[$userIndex]['role'] = $role;
                    
                    // Update password if provided
                    if (!empty($password)) {
                        $users[$userIndex]['password'] = hashPassword($password);
                    }
                    
                    // Update department name if role is department
                    if ($role === ROLE_DEPARTMENT) {
                        $users[$userIndex]['department_name'] = $department_name;
                    } else {
                        // Remove department_name if role is not department
                        unset($users[$userIndex]['department_name']);
                    }
                    
                    if (writeJsonFile(USERS_FILE, $users)) {
                        setMessage('User updated successfully', 'success');
                    } else {
                        setMessage('Failed to update user', 'error');
                    }
                }
            } else {
                setMessage('User not found', 'error');
            }
        } else {
            // Adding new user
            // Check if username already exists
            $existingUser = getUserByUsername($username);
            
            if ($existingUser) {
                setMessage('Username already exists', 'error');
            } else {
                // Create new user
                $newUser = [
                    'id' => ($role === ROLE_ADMIN ? 'admin' : ($role === ROLE_CANTEEN ? 'canteen' : 'dept')) . generateUniqueId(),
                    'username' => $username,
                    'password' => hashPassword($password),
                    'role' => $role
                ];
                
                // Add department name if role is department
                if ($role === ROLE_DEPARTMENT && !empty($department_name)) {
                    $newUser['department_name'] = $department_name;
                }
                
                $users[] = $newUser;
                
                if (writeJsonFile(USERS_FILE, $users)) {
                    setMessage('User added successfully', 'success');
                } else {
                    setMessage('Failed to add user', 'error');
                }
            }
        }
    }
    
    // Redirect to prevent form resubmission
    redirect(BASE_URL . '/admin/manage_users.php');
}

// Handle edit user request
if (isset($_GET['edit'])) {
    $userId = $_GET['edit'];
    
    // Get all users
    $users = readJsonFile(USERS_FILE);
    
    // Find user
    foreach ($users as $user) {
        if ($user['id'] === $userId) {
            $editMode = true;
            $editUser = $user;
            break;
        }
    }
    
    if (!$editUser) {
        setMessage('User not found', 'error');
        redirect(BASE_URL . '/admin/manage_users.php');
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    
    // Get all users
    $users = readJsonFile(USERS_FILE);
    
    // Find user index
    $userIndex = -1;
    foreach ($users as $index => $user) {
        if ($user['id'] === $userId) {
            $userIndex = $index;
            break;
        }
    }
    
    // Delete user if found
    if ($userIndex !== -1) {
        array_splice($users, $userIndex, 1);
        
        if (writeJsonFile(USERS_FILE, $users)) {
            setMessage('User deleted successfully', 'success');
        } else {
            setMessage('Failed to delete user', 'error');
        }
    } else {
        setMessage('User not found', 'error');
    }
    
    // Redirect
    redirect(BASE_URL . '/admin/manage_users.php');
}

// Get all users
$users = readJsonFile(USERS_FILE);

// Get message
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
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
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="md:ml-64 w-full p-4 md:p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Manage Users</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6"><?php echo $editMode ? 'Edit User' : 'Add New User'; ?></h2>
                
                <form method="post" action="">
                    <?php if ($editMode): ?>
                    <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $editMode ? htmlspecialchars($editUser['username']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <?php echo $editMode ? '(Leave blank to keep current password)' : ''; ?></label>
                        <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="role" name="role" onchange="toggleDepartmentField()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Role</option>
                            <option value="<?php echo ROLE_ADMIN; ?>" <?php echo ($editMode && $editUser['role'] === ROLE_ADMIN) ? 'selected' : ''; ?>>Admin</option>
                            <option value="<?php echo ROLE_DEPARTMENT; ?>" <?php echo ($editMode && $editUser['role'] === ROLE_DEPARTMENT) ? 'selected' : ''; ?>>Department</option>
                            <option value="<?php echo ROLE_CANTEEN; ?>" <?php echo ($editMode && $editUser['role'] === ROLE_CANTEEN) ? 'selected' : ''; ?>>Canteen</option>
                        </select>
                    </div>
                    
                    <div class="mb-4" id="department-field" style="display: <?php echo ($editMode && $editUser['role'] === ROLE_DEPARTMENT) ? 'block' : 'none'; ?>">
                        <label for="department_name" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input type="text" id="department_name" name="department_name" value="<?php echo ($editMode && isset($editUser['department_name'])) ? htmlspecialchars($editUser['department_name']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition-colors duration-300"><?php echo $editMode ? 'Update User' : 'Add User'; ?></button>
                        <?php if ($editMode): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/manage_users.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Existing Users</h2>
                
                <?php if (empty($users)): ?>
                    <p class="text-gray-600">No users found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $user['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['username']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo ucfirst($user['role']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['department_name'] ?? '-'; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo BASE_URL; ?>/admin/manage_users.php?edit=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <a href="<?php echo BASE_URL; ?>/admin/manage_users.php?delete=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleDepartmentField() {
            var roleSelect = document.getElementById('role');
            var departmentField = document.getElementById('department-field');
            
            if (roleSelect.value === '<?php echo ROLE_DEPARTMENT; ?>') {
                departmentField.style.display = 'block';
            } else {
                departmentField.style.display = 'none';
            }
        }
    </script>
</body>
</html>
