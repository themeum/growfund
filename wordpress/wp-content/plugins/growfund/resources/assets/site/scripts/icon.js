/**
 * Icon Management System
 * Simple and elegant icon swapping using preloaded icon data
 */

/**
 * Swap an icon to a different icon name
 * @param {HTMLElement} iconElement - The icon element to swap
 * @param {string} newIconName - The new icon name to display
 */
function swapIcon(iconElement, newIconName) {
  if (!iconElement || !newIconName) {
    console.warn('swapIcon: Missing required parameters', { iconElement, newIconName });
    return;
  }

  // Get preloaded icons from the first icon element on the page
  const firstIcon = document.querySelector('.growfund-icon');
  if (!firstIcon) {
    console.warn('swapIcon: No icon elements found on page');
    return;
  }

  const preloadedIcons = firstIcon.dataset.preloadedIcons;
  if (!preloadedIcons) {
    console.warn('swapIcon: No preloaded icons data found');
    return;
  }

  try {
    const icons = JSON.parse(preloadedIcons);

    if (!icons[newIconName]) {
      console.warn(`swapIcon: Icon '${newIconName}' not found in preloaded icons`);
      return;
    }

    // Update the icon content
    iconElement.innerHTML = icons[newIconName];

    // Update the icon name class
    iconElement.className = iconElement.className.replace(
      /growfund-icon--[a-zA-Z-]+/,
      `growfund-icon--${newIconName}`,
    );

    // Update the data attribute
    iconElement.dataset.iconName = newIconName;

    console.log(`Icon swapped to: ${newIconName}`);
  } catch (error) {
    console.error('swapIcon: Error parsing preloaded icons', error);
  }
}

/**
 * Get an icon element by its container
 * @param {HTMLElement} container - The container element
 * @param {string} iconName - The icon name to find (optional)
 * @returns {HTMLElement|null} The icon element or null if not found
 */
function getIconElement(container, iconName = null) {
  if (!container) return null;

  if (iconName) {
    return container.querySelector(`.growfund-icon--${iconName}`);
  }

  return container.querySelector('.growfund-icon');
}

/**
 * Swap icon in a container to a different icon
 * @param {HTMLElement} container - The container element
 * @param {string} newIconName - The new icon name
 * @param {string} oldIconName - The old icon name (optional, for targeting specific icon)
 */
function swapIconInContainer(container, newIconName, oldIconName = null) {
  const iconElement = getIconElement(container, oldIconName);
  if (iconElement) {
    swapIcon(iconElement, newIconName);
  } else {
    console.warn(`swapIconInContainer: No icon found in container`, {
      container,
      newIconName,
      oldIconName,
    });
  }
}

// Export functions for global access
window.cfIcon = {
  swapIcon,
  getIconElement,
  swapIconInContainer,
};
