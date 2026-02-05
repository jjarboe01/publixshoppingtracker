# Home Assistant Add-on File Structure

```
PublixTracker/
├── config.yaml              # Home Assistant add-on configuration
├── build.yaml              # Multi-arch build configuration
├── repository.yaml         # Repository metadata
├── Dockerfile.unified      # Docker image definition
├── run.sh                  # Home Assistant add-on entrypoint
├── docker-compose.yml      # Standalone Docker Compose (optional)
├── README.md               # Main documentation
├── DOCS.md                 # Detailed add-on documentation
├── INSTALL.md              # Installation guide
├── icon.png                # Add-on icon (256x256)
├── logo.png                # Add-on logo (optional)
│
├── GetReciepts.py          # Python receipt retrieval script
├── ViewDatabase.py         # Database viewer utility
├── requirements.txt        # Python dependencies
│
└── web/                    # Web interface files
    ├── index.php           # Dashboard
    ├── trips.php           # Shopping trips view
    ├── top-items.php       # Top items view
    ├── monthly.php         # Monthly analysis
    ├── yearly.php          # Yearly analysis
    ├── settings.php        # Configuration page
    ├── sync.php            # Manual sync page
    ├── database.php        # Raw database view
    ├── style.css           # Stylesheet
    └── init_db.php         # Database initialization
```

## Key Files for Home Assistant

### config.yaml
Defines the add-on metadata, options, and schema. Home Assistant reads this to show configuration options in the UI.

### run.sh
The entry point for Home Assistant add-ons. Uses `bashio` to read configuration from Home Assistant and sets up the environment.

### build.yaml
Specifies base images for different architectures. Allows building for ARM, x86, etc.

### Dockerfile.unified
Builds the container image with all dependencies. Modified to detect and support Home Assistant add-on mode.

### DOCS.md
Displayed in Home Assistant add-on store as the main documentation. Should include:
- Features
- Installation steps  
- Configuration options
- Usage instructions
- Troubleshooting

## Data Persistence

When running as a Home Assistant add-on:
- Data stored in: `/data/`
- Config file: `/data/config.php`
- Database: `/data/publix_tracker.db`
- Cron logs: `/data/cron.log`

These are automatically backed up with Home Assistant snapshots.

## Environment Detection

The Dockerfile checks for `bashio` to determine if running as an add-on:
```bash
if [ -f /usr/bin/bashio ]; then
    exec /run.sh  # Home Assistant add-on mode
fi
```

## Publishing Checklist

Before publishing your add-on:

- [ ] Update `config.yaml` with your repository URL
- [ ] Update `repository.yaml` with your info
- [ ] Create 256x256 `icon.png`
- [ ] Test locally with `docker compose`
- [ ] Create GitHub repository
- [ ] Build multi-arch images (optional)
- [ ] Tag release (v1.0.0)
- [ ] Update all `yourusername` references
- [ ] Test in Home Assistant
- [ ] Document any dependencies
- [ ] Add license file

## Updating the Add-on

To release updates:

1. Make changes to code
2. Update version in `config.yaml`
3. Update changelog in `DOCS.md`
4. Commit and push changes
5. Create new GitHub release
6. Users can update via Home Assistant

Home Assistant checks for updates automatically.
