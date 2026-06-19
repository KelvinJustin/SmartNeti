#!/bin/bash
set -e

# Read pinned commit from VENDOR_PINS.md
PINNED_COMMIT=$(grep -A1 "Pinned commit" VENDOR_PINS.md | grep -oE '[a-f0-9]{40}' | head -1)

if [ -z "$PINNED_COMMIT" ]; then
  echo "Error: Could not find pinned commit in VENDOR_PINS.md"
  exit 1
fi

TARGET_DIR="radiusdesk/rdcore"
REPO_URL="https://github.com/RADIUSdesk/rdcore.git"

if [ -d "$TARGET_DIR/.git" ]; then
  echo "RadiusDesk already cloned. Checking out pinned commit $PINNED_COMMIT..."
  git -C "$TARGET_DIR" fetch origin
  git -C "$TARGET_DIR" checkout "$PINNED_COMMIT"
else
  echo "Cloning RadiusDesk at pinned commit $PINNED_COMMIT..."
  rm -rf "$TARGET_DIR"
  # Blobless partial clone with no checkout: small initial download, then fetch only the pinned commit.
  git clone --filter=blob:none --no-checkout "$REPO_URL" "$TARGET_DIR"
  git -C "$TARGET_DIR" fetch origin "$PINNED_COMMIT"
  git -C "$TARGET_DIR" checkout "$PINNED_COMMIT"
fi

echo "RadiusDesk pinned at $PINNED_COMMIT"
