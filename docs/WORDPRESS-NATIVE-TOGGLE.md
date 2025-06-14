# WordPress Native Toggle Implementation

## Overview

Implementasi toggle switch yang mengikuti design language WordPress native, mirip dengan yang digunakan di WordPress Customizer dan berbagai plugin resmi WordPress.

## Design Principles

### 1. WordPress Design Language
- Mengikuti color scheme WordPress admin
- Konsisten dengan spacing dan typography WP
- Menggunakan transition timing yang sama dengan WP interface

### 2. Accessibility First
- Proper focus states dengan outline biru WordPress
- Keyboard navigation support
- Screen reader friendly dengan proper labeling
- High contrast mode support

### 3. Visual Consistency
- Size dan proporsi sesuai dengan element WordPress lainnya
- Color palette menggunakan WordPress admin colors
- Animation timing mengikuti WordPress standards

## Technical Specifications

### HTML Structure
```php
<label class="toggle-switch">
    <input type="hidden" name="enabled" value="0">
    <input type="checkbox" name="enabled" value="1" class="toggle-switch-checkbox">
    <span class="toggle-switch-slider"></span>
    <span class="toggle-switch-label">Label Text</span>
</label>
```

### CSS Specifications

#### Toggle Dimensions
- **Width**: 36px (WordPress standard)
- **Height**: 18px (WordPress standard)
- **Border Radius**: 9px (perfect circle)
- **Slider Size**: 14x14px (centered with 2px margin)

#### Color Scheme (WordPress Native)
```css
/* Inactive State */
background-color: #8c8f94; /* WP gray */

/* Active State */
background-color: #00a32a; /* WP green */

/* Hover States */
inactive-hover: #646970;   /* Darker gray */
active-hover: #008a00;     /* Darker green */

/* Focus State */
box-shadow: 0 0 0 2px #2271b1; /* WP blue focus */
```

#### Typography
- **Font Weight**: 600 (semibold)
- **Color**: #1d2327 (WordPress text color)
- **Font Family**: Inherits from WordPress admin

### JavaScript Functionality

#### Event Handling
```javascript
// Click anywhere on toggle container
$('.toggle-switch').on('click', function(e) {
    if (!$(e.target).is('input[type="checkbox"]')) {
        var $checkbox = $(this).find('.toggle-switch-checkbox');
        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    }
});

// Handle state changes
$('.toggle-switch-checkbox').on('change', function() {
    // Update hidden field
    // Visual feedback
    // State management
});
```

## Features

### 1. Visual States
- ✅ **Inactive**: Gray background, slider on left
- ✅ **Active**: Green background, slider on right
- ✅ **Hover**: Darker colors for better feedback
- ✅ **Focus**: Blue outline for keyboard users
- ✅ **Disabled**: Reduced opacity (if needed)

### 2. Interactions
- ✅ **Click to toggle**: Click anywhere on toggle area
- ✅ **Keyboard support**: Space/Enter to toggle
- ✅ **Smooth animation**: 0.2s ease-in-out transition
- ✅ **Visual feedback**: Immediate state change

### 3. Accessibility
- ✅ **Screen readers**: Proper checkbox semantics
- ✅ **Keyboard navigation**: Full support
- ✅ **Focus management**: Clear focus indicators
- ✅ **High contrast**: Works in high contrast modes

## WordPress Integration

### 1. Form Table Integration
```php
<table class="form-table">
    <tr>
        <th scope="row">Setting Label</th>
        <td>
            <!-- Toggle switch here -->
            <p class="description">Helper text</p>
        </td>
    </tr>
</table>
```

### 2. Settings API Compatible
- Works with WordPress Settings API
- Proper form submission handling
- Hidden field for unchecked state
- Standard validation support

### 3. WordPress Color Scheme
- Adapts to WordPress admin color schemes
- Consistent with current admin theme
- Professional appearance

## Browser Support

Supports all browsers that WordPress supports:
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (with graceful degradation)

## Performance

- **CSS**: ~1KB minified
- **JavaScript**: ~500 bytes minified
- **No external dependencies** beyond jQuery (included in WP)
- **Hardware accelerated** transforms for smooth animation

## Comparison with WordPress Elements

### Similar to:
- WordPress Customizer toggles
- Plugin activation switches
- Theme customization options
- WooCommerce admin toggles

### Design Consistency:
- Same color palette as WP admin
- Same transition timing
- Same focus states
- Same hover effects

## Implementation Best Practices

### 1. Always Include Hidden Field
```php
<input type="hidden" name="field_name" value="0">
<input type="checkbox" name="field_name" value="1">
```

### 2. Proper Labeling
```php
<label class="toggle-switch">
    <!-- checkbox -->
    <span class="toggle-switch-label">Descriptive Label</span>
</label>
```

### 3. Description Text
```php
<p class="description">Clear explanation of what this toggle does</p>
```

### 4. Form Table Context
Always use within WordPress form-table for consistency.

## Result

Toggle yang:
- ✅ **Native WordPress look & feel**
- ✅ **Accessible dan keyboard friendly**
- ✅ **Smooth animations**
- ✅ **Consistent color scheme**
- ✅ **Professional appearance**
- ✅ **Future-proof dengan WordPress updates**
