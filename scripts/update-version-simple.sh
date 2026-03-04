#!/bin/bash

###############################################################################
# LeadCapture Form Plugin - Simple Version Update Script
#
# A robust version updater that handles macOS sed quirks using perl.
#
# Usage: ./scripts/update-version-simple.sh <new-version>
# Example: ./scripts/update-version-simple.sh 1.0.1
#
# @package LeadCaptureForm
# @since 1.0.0
# @author Silver Assist
# @version 1.0.0
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Validate input
if [ $# -eq 0 ]; then
    print_error "No version specified"
    echo "Usage: $0 <new-version>"
    echo "Example: $0 1.0.1"
    exit 1
fi

NEW_VERSION="$1"
shift

# Parse flags
NO_CONFIRM=false
FORCE=false
while [[ $# -gt 0 ]]; do
    case "$1" in
        --no-confirm) NO_CONFIRM=true ;;
        --force) FORCE=true ;;
    esac
    shift
done

# Validate version format
if ! [[ $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    print_error "Invalid version format. Use semantic versioning (e.g., 1.0.1)"
    exit 1
fi

# Get project root
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

print_status "Updating LeadCapture Form Plugin to version ${NEW_VERSION}"
print_status "Project root: ${PROJECT_ROOT}"

# Get current version
CURRENT_VERSION=$(grep -o "Version: [0-9]\+\.[0-9]\+\.[0-9]\+" "${PROJECT_ROOT}/leadcapture-form.php" | cut -d' ' -f2)

if [ -z "$CURRENT_VERSION" ]; then
    print_error "Could not detect current version"
    exit 1
fi

print_status "Current version: ${CURRENT_VERSION}"
print_status "New version: ${NEW_VERSION}"

# Skip if version is already the same and not forced
if [ "$CURRENT_VERSION" = "$NEW_VERSION" ] && [ "$FORCE" = false ]; then
    print_success "Version is already ${NEW_VERSION} — nothing to do"
    exit 0
fi

# Confirm with user
if [ "$NO_CONFIRM" = false ]; then
    echo ""
    read -p "$(echo -e ${YELLOW}[CONFIRM]${NC} Update version from ${CURRENT_VERSION} to ${NEW_VERSION}? [y/N]: )" -n 1 -r
    echo ""

    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Version update cancelled"
        exit 0
    fi
fi

echo ""
print_status "Starting version update process..."

# Function to update file using perl (more reliable than sed on macOS)
update_file() {
    local file="$1"
    local pattern="$2"
    
    if [ -f "$file" ]; then
        perl -i -pe "$pattern" "$file"
        return $?
    else
        return 1
    fi
}

# 1. Update main plugin file
print_status "Updating main plugin file..."

update_file "${PROJECT_ROOT}/leadcapture-form.php" "s/Version: [0-9]+\.[0-9]+\.[0-9]+/Version: ${NEW_VERSION}/g"
update_file "${PROJECT_ROOT}/leadcapture-form.php" "s/define\\(\"LEADCAPTURE_FORM_VERSION\", \"[0-9]+\.[0-9]+\.[0-9]+\"\\)/define(\"LEADCAPTURE_FORM_VERSION\", \"${NEW_VERSION}\")/g"
update_file "${PROJECT_ROOT}/leadcapture-form.php" "s/\@version [0-9]+\.[0-9]+\.[0-9]+/\@version ${NEW_VERSION}/g"

print_success "Main plugin file updated"

# 2. Update PHP files
print_status "Updating PHP files..."

find "${PROJECT_ROOT}/includes" -name "*.php" -print0 | while IFS= read -r -d '' file; do
    if grep -q "@version" "$file"; then
        update_file "$file" "s/\@version [0-9]+\.[0-9]+\.[0-9]+/\@version ${NEW_VERSION}/g"
        print_status "  Updated $(basename "$file")"
    fi
done

print_success "PHP files updated"

# 3. Update CSS files
print_status "Updating CSS files..."

find "${PROJECT_ROOT}/assets/css" -name "*.css" -print0 | while IFS= read -r -d '' file; do
    if grep -q "@version" "$file"; then
        update_file "$file" "s/\@version [0-9]+\.[0-9]+\.[0-9]+/\@version ${NEW_VERSION}/g"
        print_status "  Updated $(basename "$file")"
    fi
done

print_success "CSS files updated"

# 4. Update JavaScript files
print_status "Updating JavaScript files..."

find "${PROJECT_ROOT}/assets/js" "${PROJECT_ROOT}/blocks" -name "*.js" -print0 | while IFS= read -r -d '' file; do
    if grep -q "@version" "$file"; then
        update_file "$file" "s/\@version [0-9]+\.[0-9]+\.[0-9]+/\@version ${NEW_VERSION}/g"
        print_status "  Updated $(basename "$file")"
    fi
done

print_success "JavaScript files updated"

# 5. Update block.json
print_status "Updating block metadata..."

find "${PROJECT_ROOT}/blocks" -name "block.json" -print0 | while IFS= read -r -d '' file; do
    if grep -q "\"version\":" "$file"; then
        update_file "$file" "s/\"version\": \"[0-9]+\.[0-9]+\.[0-9]+\"/\"version\": \"${NEW_VERSION}\"/g"
        print_status "  Updated $(basename "$file")"
    fi
done

print_success "Block metadata updated"

# 6. Update this script
print_status "Updating version scripts..."
update_file "${PROJECT_ROOT}/scripts/update-version-simple.sh" "s/\@version [0-9]+\.[0-9]+\.[0-9]+/\@version ${NEW_VERSION}/g"

print_success "Version scripts updated"

echo ""
print_success "Version update completed successfully!"
echo ""
print_status "Summary of changes:"
echo "  - Main plugin file: leadcapture-form.php"
echo "  - PHP files: includes/**/*.php"
echo "  - CSS files: assets/css/*.css"
echo "  - JavaScript files: assets/js/*.js, blocks/**/*.js"
echo "  - Block metadata: blocks/**/block.json"
echo "  - Update scripts: scripts/update-version*.sh"
echo ""
print_status "Next steps:"
echo "  1. Verify changes: ./scripts/check-versions.sh"
echo "  2. Review the changes: git diff"
echo "  3. Commit changes: git add . && git commit -m 'chore: update version to ${NEW_VERSION}'"
echo "  4. Create tag: git tag v${NEW_VERSION}"
echo "  5. Push changes: git push origin main && git push origin v${NEW_VERSION}"
echo ""
print_warning "Remember: This script only updates @version tags, not @since tags!"
