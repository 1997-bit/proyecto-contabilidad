<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Priority+ Responsive Navbar</title>
    <style>
        :root {
            --color-background: #ffffff;
            --color-surface: #f3f4f6;
            --color-text-primary: #111827;
            --color-text-secondary: #4b5563;
            --color-primary: #3b82f6;
            --color-border: #e5e7eb;
            --font-family-sans-serif: ui-sans-serif, system-ui, -apple-system, sans-serif;
            --navbar-height: 70px;
            --container-width: 1200px;
            --transition-speed: 0.2s;
            --border-radius: 6px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: var(--font-family-sans-serif); background-color: #f9fafb; line-height: 1.6; }
        .content { min-height: 150vh; max-width: var(--container-width); margin: 0 auto; padding: calc(var(--navbar-height) + 2rem) 1.5rem; }
        ul { list-style: none; margin: 0; padding: 0; }
        a { text-decoration: none; color: var(--color-text-secondary); transition: color var(--transition-speed) ease; }

        .navbar {
            height: var(--navbar-height); display: flex; align-items: center;
            position: sticky; top: 0; z-index: 1000;
            background-color: var(--color-background); border-bottom: 1px solid var(--color-border);
        }
        .navbar__container {
            display: flex; align-items: center; justify-content: space-between;
            width: 100%; max-width: var(--container-width);
            margin: 0 auto; padding: 0 1.5rem;
        }
        .navbar__brand { font-size: 1.5rem; font-weight: 600; color: var(--color-text-primary); margin-right: 2rem; }

        .navbar__nav { flex-grow: 1; }
        .primary-nav { display: flex; align-items: center; }

        .nav-link {
            display: block; padding: 0.5rem 1rem; white-space: nowrap; /* Prevent links from wrapping */
        }
        .nav-link:hover { color: var(--color-primary); }

        /* The "More" dropdown item */
        #more-menu-item {
            position: relative; /* Anchor for the dropdown */
            display: none; /* Hidden by default, shown by JS */
        }

        .more-dropdown {
            position: absolute; top: 100%; right: 0;
            min-width: 200px;
            background-color: var(--color-background);
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            
            /* Hidden by default */
            opacity: 0; visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.2s, transform 0.2s;
            z-index: 1001;
        }

        /* Show the dropdown on hover/focus */
        #more-menu-item:hover .more-dropdown,
        #more-menu-item:focus-within .more-dropdown {
            opacity: 1; visibility: visible;
            transform: translateY(0);
        }

        .more-dropdown li { border-bottom: 1px solid var(--color-border); }
        .more-dropdown li:last-child { border-bottom: none; }
        .more-dropdown .nav-link { width: 100%; padding: 0.75rem 1rem; }
    </style>
</head>
<body>
    <header class="navbar" id="navbar">
        <div class="navbar__container">
            <a href="#" class="navbar__brand">PriorityNav</a>
            <nav class="navbar__nav">
                <ul class="primary-nav" id="primary-nav">
                    <!-- Nav items will be dynamically moved here by JS -->
                    <!-- High priority links should come first in this initial HTML -->
                    <li><a href="#" class="nav-link">Dashboard</a></li>
                    <li><a href="#" class="nav-link">Analytics</a></li>
                    <li><a href="#" class="nav-link">Reports</a></li>
                    <li><a href="#" class="nav-link">Projects</a></li>
                    <li><a href="#" class="nav-link">Tasks</a></li>
                    <li><a href="#" class="nav-link">Users</a></li>
                    <li><a href="#" class="nav-link">Settings</a></li>
                    <li><a href="#" class="nav-link">Support</a></li>
                    
                    <!-- The "More" dropdown, initially empty and hidden -->
                    <li id="more-menu-item">
                        <a href="#" class="nav-link" aria-haspopup="true" aria-expanded="false">More &#x25BE;</a>
                        <ul class="more-dropdown" id="more-dropdown"></ul>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const navbar = document.getElementById('navbar');
        const primaryNav = document.getElementById('primary-nav');
        const moreMenuItem = document.getElementById('more-menu-item');
        const moreDropdown = document.getElementById('more-dropdown');

        if (!primaryNav || !moreMenuItem || !moreDropdown) return;
        
        // This function does the heavy lifting of organizing the navigation
        const reorganizeNav = () => {
            // Get the total width of the navigation container
            // The padding is subtracted to get the usable space
            const navWidth = navbar.clientWidth - (parseInt(getComputedStyle(navbar.querySelector('.navbar__container')).paddingLeft) * 2);

            // Get the width of elements that are always visible
            const brandWidth = document.querySelector('.navbar__brand')?.offsetWidth || 0;
            // The width of the "More" button (if it's visible)
            const moreMenuWidth = moreMenuItem.style.display === 'block' ? moreMenuItem.offsetWidth : 0;
            
            const availableSpace = navWidth - brandWidth - moreMenuWidth;
            
            // Get all primary nav items EXCEPT the "More" menu
            let primaryItems = Array.from(primaryNav.children).filter(item => item.id !== 'more-menu-item');
            
            let currentWidth = 0;
            primaryItems.forEach(item => {
                currentWidth += item.offsetWidth;
            });

            // --- Shrink: Move items from primary to "More" ---
            while (currentWidth > availableSpace && primaryItems.length > 0) {
                // Get the last item from the primary nav (that isn't "More")
                const itemToMove = primaryItems.pop();
                
                // Move it to the start of the "More" dropdown
                moreDropdown.prepend(itemToMove);
                
                // Recalculate the width
                currentWidth -= itemToMove.offsetWidth;
            }

            // --- Expand: Move items from "More" back to primary ---
            // Get the first item in the dropdown
            const firstMoreItem = moreDropdown.querySelector('li');
            if (firstMoreItem) {
                // Check if we have space to bring it back
                if (currentWidth + firstMoreItem.offsetWidth < availableSpace) {
                    primaryNav.insertBefore(firstMoreItem, moreMenuItem);
                }
            }

            // --- Toggle "More" button visibility ---
            // If the dropdown has items, show the "More" button. Otherwise, hide it.
            moreMenuItem.style.display = moreDropdown.children.length > 0 ? 'block' : 'none';
        };

        // Use ResizeObserver for efficient monitoring of size changes
        const observer = new ResizeObserver(reorganizeNav);
        observer.observe(navbar);

        // Run it once on initial load
        reorganizeNav();
    });
    </script>
</body>
</html>