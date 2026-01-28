// Stimulus Bootstrap - Simplified version without @symfony/stimulus-bundle
// This is a simple fallback when the symfony stimulus bundle is not installed

// Check if Stimulus controllers directory exists and initialize them
// This allows the cart controller and other stimulus controllers to work
try {
    // Import any custom controllers here if needed
    // For example: import CartController from './controllers/cart_controller.js';
    console.log('Stimulus bootstrap loaded (simplified)');
} catch (error) {
    console.warn('Stimulus controllers not available:', error);
}

