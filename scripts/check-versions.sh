#!/bin/bash

###############################################################################
# LeadCapture Form Plugin - Version Check Script
#
# Checks and displays current version numbers across all plugin files.
# Useful for verifying version consistency before and after updates.
#
# Usage: ./scripts/check-versions.sh
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
CYAN='\033[0;36m'
NC='\033[0m'

print_header() {
    echo -e "${CYAN}=== $1 ===${NC}"
}

print_file() {
    echo -e "${BLUE}📄 $1${NC}"
}

print_version() {
    echo -e "   ${GREEN}Version: $1${NC}"
}

print_error() {
    echo -e "   ${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "   ${YELLOW}⚠️  $1${NC}"
}

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo -e "${CYAN}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    VERSION CHECK REPORT                     ║"
echo "║                  LeadCapture Form Plugin                    ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

if [ ! -f "${PROJECT_ROOT}/leadcapture-form.php" ]; then
    print_error "Main plugin file not found. Make sure you're running this from the project root."
    exit 1
fi

print_header "Main Plugin File"
print_file "leadcapture-form.php"

PLUGIN_HEADER_VERSION=$(grep -o "Version: [0-9]\+\.[0-9]\+\.[0-9]\+" "${PROJECT_ROOT}/leadcapture-form.php" | cut -d' ' -f2)
PLUGIN_CONSTANT_VERSION=$(grep -o "LEADCAPTURE_FORM_VERSION.*[0-9]\+\.[0-9]\+\.[0-9]\+" "${PROJECT_ROOT}/leadcapture-form.php" | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")
PLUGIN_DOCBLOCK_VERSION=$(grep -o "@version [0-9]\+\.[0-9]\+\.[0-9]\+" "${PROJECT_ROOT}/leadcapture-form.php" | cut -d' ' -f2)

if [ -n "$PLUGIN_HEADER_VERSION" ]; then
    print_version "Plugin Header: $PLUGIN_HEADER_VERSION"
else
    print_error "Plugin header version not found"
fi

if [ -n "$PLUGIN_CONSTANT_VERSION" ]; then
    print_version "Plugin Constant: $PLUGIN_CONSTANT_VERSION"
else
    print_error "Plugin constant version not found"
fi

if [ -n "$PLUGIN_DOCBLOCK_VERSION" ]; then
    print_version "DocBlock: $PLUGIN_DOCBLOCK_VERSION"
else
    print_error "DocBlock version not found"
fi

MAIN_VERSION="$PLUGIN_HEADER_VERSION"

echo ""
print_header "PHP Files (includes/)"

find "${PROJECT_ROOT}/includes" -name "*.php" -type f | sort | while read -r file; do
    filename=$(basename "$file")
    print_file "$filename"
    
    version=$(grep -o "@version [0-9]\+\.[0-9]\+\.[0-9]\+" "$file" 2>/dev/null | cut -d' ' -f2)
    
    if [ -n "$version" ]; then
        if [ "$version" = "$MAIN_VERSION" ]; then
            print_version "$version ✓"
        else
            print_warning "$version (differs from main: $MAIN_VERSION)"
        fi
    else
        print_error "No @version tag found"
    fi
done

echo ""
print_header "CSS Files (assets/css/)"

find "${PROJECT_ROOT}/assets/css" -name "*.css" -type f | sort | while read -r file; do
    filename=$(basename "$file")
    print_file "$filename"
    
    version=$(grep -o "@version [0-9]\+\.[0-9]\+\.[0-9]\+" "$file" 2>/dev/null | cut -d' ' -f2)
    
    if [ -n "$version" ]; then
        if [ "$version" = "$MAIN_VERSION" ]; then
            print_version "$version ✓"
        else
            print_warning "$version (differs from main: $MAIN_VERSION)"
        fi
    else
        print_error "No @version tag found"
    fi
done

echo ""
print_header "JavaScript Files"

if [ -d "${PROJECT_ROOT}/assets/js" ]; then
    find "${PROJECT_ROOT}/assets/js" -name "*.js" -type f | sort | while read -r file; do
        filename="assets/js/$(basename "$file")"
        print_file "$filename"
        
        version=$(grep -o "@version [0-9]\+\.[0-9]\+\.[0-9]\+" "$file" 2>/dev/null | cut -d' ' -f2)
        
        if [ -n "$version" ]; then
            if [ "$version" = "$MAIN_VERSION" ]; then
                print_version "$version ✓"
            else
                print_warning "$version (differs from main: $MAIN_VERSION)"
            fi
        else
            print_error "No @version tag found"
        fi
    done
fi

if [ -d "${PROJECT_ROOT}/blocks" ]; then
    find "${PROJECT_ROOT}/blocks" -name "*.js" -type f | sort | while read -r file; do
        filename="blocks/$(basename "$(dirname "$file")")/$(basename "$file")"
        print_file "$filename"
        
        version=$(grep -o "@version [0-9]\+\.[0-9]\+\.[0-9]\+" "$file" 2>/dev/null | cut -d' ' -f2)
        
        if [ -n "$version" ]; then
            if [ "$version" = "$MAIN_VERSION" ]; then
                print_version "$version ✓"
            else
                print_warning "$version (differs from main: $MAIN_VERSION)"
            fi
        else
            print_error "No @version tag found"
        fi
    done
fi

echo ""
print_header "Block Metadata (block.json)"

find "${PROJECT_ROOT}/blocks" -name "block.json" -type f | sort | while read -r file; do
    filename="blocks/$(basename "$(dirname "$file")")/$(basename "$file")"
    print_file "$filename"
    
    version=$(grep -o "\"version\": \"[0-9]\+\.[0-9]\+\.[0-9]\+\"" "$file" 2>/dev/null | cut -d'"' -f4)
    
    if [ -n "$version" ]; then
        if [ "$version" = "$MAIN_VERSION" ]; then
            print_version "$version ✓"
        else
            print_warning "$version (differs from main: $MAIN_VERSION)"
        fi
    else
        print_error "No version found"
    fi
done

echo ""
print_header "Summary"

if [ -n "$MAIN_VERSION" ]; then
    echo -e "${GREEN}✓ Main plugin version: $MAIN_VERSION${NC}"
else
    echo -e "${RED}❌ Could not determine main plugin version${NC}"
fi

echo ""
echo -e "${BLUE}Tips:${NC}"
echo "  Use ${YELLOW}./scripts/update-version-simple.sh <version>${NC} to update all versions"
echo "  Green checkmarks indicate files matching the main version"
echo "  Warnings indicate version mismatches that may need attention"
