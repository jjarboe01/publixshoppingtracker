import imaplib
import email
from email.header import decode_header
import os
from getpass import getpass
import re
from decimal import Decimal
import sqlite3
from datetime import datetime


def get_imap_server(email_address, provider=None):
    """
    Get IMAP server configuration for email provider.
    
    Args:
        email_address: Email address
        provider: Optional provider override
        
    Returns:
        Tuple of (server, port)
    """
    # Provider-specific IMAP servers
    imap_servers = {
        'gmail': ('imap.gmail.com', 993),
        'outlook': ('outlook.office365.com', 993),
        'hotmail': ('outlook.office365.com', 993),
        'yahoo': ('imap.mail.yahoo.com', 993),
        'icloud': ('imap.mail.me.com', 993),
        'aol': ('imap.aol.com', 993),
    }
    
    # If provider specified, use it
    if provider and provider.lower() in imap_servers:
        return imap_servers[provider.lower()]
    
    # Auto-detect from email domain
    domain = email_address.split('@')[-1].lower()
    
    if 'gmail.com' in domain:
        return imap_servers['gmail']
    elif 'outlook.com' in domain or 'hotmail.com' in domain or 'live.com' in domain:
        return imap_servers['outlook']
    elif 'yahoo.com' in domain:
        return imap_servers['yahoo']
    elif 'icloud.com' in domain or 'me.com' in domain:
        return imap_servers['icloud']
    elif 'aol.com' in domain:
        return imap_servers['aol']
    else:
        # Default to standard IMAP SSL port
        return (f'imap.{domain}', 993)

def connect_to_gmail(email_address, password, provider=None):
    """
    Connect to email provider using IMAP.
    
    Args:
        email_address: Your email address
        password: Your email app-specific password
        provider: Optional email provider (gmail, outlook, yahoo, icloud, aol)
        
    Returns:
        IMAP connection object
    """
    try:
        # Get IMAP server configuration
        server, port = get_imap_server(email_address, provider)
        print(f"Connecting to {server}:{port}...")
        
        # Connect to IMAP server
        imap = imaplib.IMAP4_SSL(server, port)
        imap.login(email_address, password)
        print(f"Successfully connected to {email_address}")
        return imap
    except Exception as e:
        print(f"Failed to connect: {e}")
        print(f"\nTroubleshooting tips:")
        print(f"  - Make sure you're using an app-specific password (not your regular password)")
        print(f"  - For Gmail: https://myaccount.google.com/apppasswords")
        print(f"  - For Outlook/Hotmail: https://account.microsoft.com/security")
        print(f"  - For Yahoo: https://login.yahoo.com/account/security")
        print(f"  - For iCloud: https://appleid.apple.com/account/manage")
        return None


def create_gmail_folder(imap, folder_name):
    """
    Create a Gmail folder (label) if it doesn't exist.
    
    Args:
        imap: IMAP connection object
        folder_name: Name of the folder to create
        
    Returns:
        Boolean indicating success
    """
    try:
        # List all folders
        status, folders = imap.list()
        
        if status == "OK":
            # Check if folder exists
            folder_exists = False
            for folder in folders:
                if folder_name.encode() in folder:
                    folder_exists = True
                    break
            
            if not folder_exists:
                # Create the folder
                status, result = imap.create(folder_name)
                if status == "OK":
                    print(f"✓ Created folder: {folder_name}")
                    return True
                else:
                    print(f"✗ Failed to create folder: {folder_name}")
                    return False
            else:
                print(f"✓ Folder already exists: {folder_name}")
                return True
    except Exception as e:
        print(f"Error creating folder: {e}")
        return False


def move_email_to_folder(imap, email_id, folder_name):
    """
    Move an email to a specific folder.
    
    Args:
        imap: IMAP connection object
        email_id: Email ID to move
        folder_name: Destination folder name
        
    Returns:
        Boolean indicating success
    """
    try:
        # Copy email to the destination folder
        status, result = imap.copy(email_id, folder_name)
        
        if status == "OK":
            # Mark original email for deletion
            imap.store(email_id, '+FLAGS', '\\Deleted')
            # Expunge to actually delete
            imap.expunge()
            return True
        else:
            print(f"Failed to move email {email_id.decode()}")
            return False
    except Exception as e:
        print(f"Error moving email: {e}")
        return False


def search_publix_emails(imap):
    """
    Search for Publix-related emails to help identify the correct sender and subject.
    """
    print("\n🔍 Searching for Publix-related emails...\n")
    
    imap.select("INBOX")
    
    # Try multiple search patterns
    search_patterns = [
        ('FROM "publix"', "Emails from any Publix address"),
        ('SUBJECT "receipt"', "Emails with 'receipt' in subject"),
        ('SUBJECT "reciept"', "Emails with 'reciept' (misspelled) in subject"),
        ('FROM "no-reply@exact.publix.com"', "Emails from no-reply@exact.publix.com"),
        ('FROM "publix.com"', "Emails from any @publix.com address"),
    ]
    
    found_any = False
    for pattern, description in search_patterns:
        status, message_ids = imap.search(None, pattern)
        if status == "OK" and message_ids[0]:
            email_ids = message_ids[0].split()
            if email_ids:
                found_any = True
                print(f"✓ {description}: Found {len(email_ids)} emails")
                
                # Show details of first few emails
                for i, email_id in enumerate(email_ids[:3]):
                    status, msg_data = imap.fetch(email_id, "(RFC822)")
                    if status == "OK":
                        for response_part in msg_data:
                            if isinstance(response_part, tuple):
                                msg = email.message_from_bytes(response_part[1])
                                subject, encoding = decode_header(msg["Subject"])[0]
                                if isinstance(subject, bytes):
                                    subject = subject.decode(encoding if encoding else "utf-8")
                                from_addr = msg.get("From")
                                date = msg.get("Date")
                                print(f"    Example {i+1}: From: {from_addr}")
                                print(f"              Subject: {subject}")
                                print(f"              Date: {date}")
            else:
                print(f"✗ {description}: None found")
        else:
            print(f"✗ {description}: None found")
    
    if not found_any:
        print("\n⚠️  No Publix-related emails found. Please check:")
        print("   - Are you using the correct Gmail account?")
        print("   - Do you have Publix receipt emails in your inbox?")
    
    return found_any


def get_publix_receipts(imap, sender=None, subject=None):
    """
    Retrieve all emails from no-reply@exact.publix.com with subject "Your Publix reciept"
    
    Args:
        imap: IMAP connection object
        sender: Optional custom sender email
        subject: Optional custom subject text
        
    Returns:
        List of email messages
    """
    try:
        # Select the mailbox (INBOX)
        imap.select("INBOX")
        
        # Use provided sender/subject or defaults
        sender = sender or "no-reply@exact.publix.com"
        subject = subject or "Your Publix receipt."
        
        # Search for emails from the specific sender with the specific subject
        # Note: IMAP search is case-insensitive for SUBJECT
        search_criteria = f'(FROM "{sender}" SUBJECT "{subject}")'
        print(f"Searching with criteria: {search_criteria}")
        status, message_ids = imap.search(None, search_criteria)
        
        if status != "OK":
            print("Failed to search emails")
            return []
        
        # Get the list of email IDs
        email_ids = message_ids[0].split()
        print(f"Found {len(email_ids)} Publix receipt emails")
        
        emails = []
        
        # Fetch each email
        for email_id in email_ids:
            status, msg_data = imap.fetch(email_id, "(RFC822)")
            
            if status != "OK":
                print(f"Failed to fetch email ID {email_id}")
                continue
            
            # Parse the email
            for response_part in msg_data:
                if isinstance(response_part, tuple):
                    msg = email.message_from_bytes(response_part[1])
                    
                    # Decode subject
                    subject, encoding = decode_header(msg["Subject"])[0]
                    if isinstance(subject, bytes):
                        subject = subject.decode(encoding if encoding else "utf-8")
                    
                    # Get sender
                    from_addr = msg.get("From")
                    
                    # Get date
                    date = msg.get("Date")
                    
                    # Get email body
                    body = get_email_body(msg)
                    
                    email_info = {
                        "id": email_id.decode(),
                        "from": from_addr,
                        "subject": subject,
                        "date": date,
                        "body": body,
                        "message": msg
                    }
                    
                    emails.append(email_info)
                    print(f"Retrieved email from {date}")
        
        return emails
        
    except Exception as e:
        print(f"Error retrieving emails: {e}")
        return []


def get_email_body(msg):
    """
    Extract the email body from an email message.
    Prioritizes HTML version for Publix receipts.
    
    Args:
        msg: Email message object
        
    Returns:
        Email body as string
    """
    body = ""
    html_body = ""
    text_body = ""
    
    if msg.is_multipart():
        # Iterate through email parts
        for part in msg.walk():
            content_type = part.get_content_type()
            content_disposition = str(part.get("Content-Disposition"))
            
            # Get text/plain and text/html
            if content_type == "text/plain" and "attachment" not in content_disposition:
                try:
                    text_body = part.get_payload(decode=True).decode()
                except:
                    pass
            elif content_type == "text/html" and "attachment" not in content_disposition:
                try:
                    html_body = part.get_payload(decode=True).decode()
                except:
                    pass
    else:
        # Not multipart - get payload directly
        try:
            content_type = msg.get_content_type()
            body = msg.get_payload(decode=True).decode()
            if content_type == "text/html":
                html_body = body
            else:
                text_body = body
        except:
            body = msg.get_payload()
    
    # Prefer HTML body for Publix receipts (contains <pre> formatted receipt)
    if html_body:
        return html_body
    elif text_body:
        return text_body
    else:
        return body


def save_attachments(msg, email_id, output_dir="receipts"):
    """
    Save any attachments from the email.
    
    Args:
        msg: Email message object
        email_id: Email ID for naming
        output_dir: Directory to save attachments
    """
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    for part in msg.walk():
        if part.get_content_disposition() == "attachment":
            filename = part.get_filename()
            if filename:
                # Decode filename if needed
                decoded_filename, encoding = decode_header(filename)[0]
                if isinstance(decoded_filename, bytes):
                    decoded_filename = decoded_filename.decode(encoding if encoding else "utf-8")
                
                filepath = os.path.join(output_dir, f"{email_id}_{decoded_filename}")
                
                # Save the attachment
                with open(filepath, "wb") as f:
                    f.write(part.get_payload(decode=True))
                
                print(f"Saved attachment: {filepath}")


def init_database(db_path="publix_tracker.db"):
    """
    Initialize the SQLite database and create tables if they don't exist.
    
    Args:
        db_path: Path to the database file
        
    Returns:
        Database connection object
    """
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Create purchases table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS purchases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_date TEXT NOT NULL,
            item_name TEXT NOT NULL,
            price REAL NOT NULL,
            on_sale BOOLEAN NOT NULL,
            taxable BOOLEAN,
            email_id TEXT,
            savings REAL DEFAULT 0.0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    # Create index for faster queries
    cursor.execute('''
        CREATE INDEX IF NOT EXISTS idx_purchase_date ON purchases(purchase_date)
    ''')
    cursor.execute('''
        CREATE INDEX IF NOT EXISTS idx_item_name ON purchases(item_name)
    ''')
    
    conn.commit()
    print(f"Database initialized: {db_path}")
    return conn


def is_receipt_processed(conn, email_id):
    """
    Check if a receipt has already been processed.
    
    Args:
        conn: Database connection
        email_id: Email ID to check
        
    Returns:
        Boolean indicating if receipt was already processed
    """
    cursor = conn.cursor()
    cursor.execute('''
        SELECT COUNT(*) FROM purchases WHERE email_id = ?
    ''', (email_id,))
    count = cursor.fetchone()[0]
    return count > 0


def insert_purchase(conn, purchase_date, item_name, price, on_sale, taxable=None, email_id=None, savings=0.0):
    """
    Insert a purchase record into the database.
    
    Args:
        conn: Database connection
        purchase_date: Date of purchase (string or datetime)
        item_name: Name of the item
        price: Price of the item (if on sale, should be 0.00)
        on_sale: Boolean indicating if item was on sale
        taxable: Boolean indicating if item is taxable (optional)
        email_id: Email ID for reference (optional)
        savings: Total savings amount for this receipt (optional)
    """
    cursor = conn.cursor()
    
    # Convert datetime to string if needed
    if isinstance(purchase_date, datetime):
        purchase_date = purchase_date.strftime('%Y-%m-%d')
    
    cursor.execute('''
        INSERT INTO purchases (purchase_date, item_name, price, on_sale, taxable, email_id, savings)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ''', (purchase_date, item_name, price, on_sale, taxable, email_id, savings))
    
    conn.commit()


def parse_receipt_items(email_body):
    """
    Parse the receipt email body to extract purchased items, prices, and tax status.
    Handles HTML email format with receipt in <pre> tag.
    
    Args:
        email_body: The email body text (HTML or plain text)
        
    Returns:
        List of dictionaries containing item details
    """
    items = []
    
    # Extract content from <pre> tag if HTML
    if '<pre>' in email_body:
        import re as regex_module
        pre_match = regex_module.search(r'<pre>(.*?)</pre>', email_body, regex_module.DOTALL)
        if pre_match:
            receipt_text = pre_match.group(1)
        else:
            receipt_text = email_body
    else:
        receipt_text = email_body
    
    lines = receipt_text.split('\n')
    
    # Track items and their promotions
    current_items = []
    skip_next_negative = False  # Flag to skip next line if it's negative (for voided items)
    
    for i, line in enumerate(lines):
        # Skip empty lines
        if not line.strip():
            continue
        
        # Stop parsing at order total
        if 'Order Total' in line or 'Grand Total' in line:
            break
        
        # Skip lines that are clearly not items
        if any(skip in line for skip in ['Receipt ID:', 'Customer ID', 'Your cashier', 
                                          'Savings Summary', 'Reference #', 'Trace #']):
            continue
        
        # Check if next line is a quantity pricing line
        is_qty_line_next = False
        if i + 1 < len(lines):
            next_line = lines[i + 1]
            if re.match(r'^\s+\d+\s+@', next_line):
                is_qty_line_next = True
        
        # Check for "You saved:" lines first (with $ sign in price)
        # Format: " You saved: $8.79" or "  You Saved: $5.00"
        savings_match = re.match(r'^\s+You [Ss]aved:\s+\$(-?\d+\.\d{2})', line, re.IGNORECASE)
        if savings_match:
            savings_amount = float(savings_match.group(1))
            if current_items:
                # If the previous item has price 0.00, it's likely a BOGO free item
                # Apply savings to the item before it (the one that was charged)
                if len(current_items) >= 2 and current_items[-1]['price'] == 0.00:
                    # Mark both items as on sale (BOGO)
                    current_items[-2]['on_sale'] = True
                    current_items[-1]['on_sale'] = True
                    # Split the savings between both items
                    savings_per_item = abs(savings_amount) / 2
                    current_items[-2]['promotion_amount'] += savings_per_item
                    current_items[-1]['promotion_amount'] += savings_per_item
                else:
                    # Normal promotion on single item
                    current_items[-1]['on_sale'] = True
                    current_items[-1]['promotion_amount'] += abs(savings_amount)
            continue
        
        # Match item lines: Two possible formats:
        # Format 1: "  ITEM NAME              PRICE   TAX"  (2+ spaces prefix, price then tax)
        # Format 2: "ITEM NAME   TAX         PRICE"  (no prefix, tax then price)
        # Example 1: "  CALIFIA UNSWT VAN             4.89   F"
        # Example 2: "Newmans Pizza Four Chee F          8.79"
        # Also match negative prices for promotions: "  Promotion            -1.50   F"
        
        # Try Format 1 first (with leading spaces, price before tax)
        item_match = re.match(r'^\s{2,}(.+?)\s+(-?\d+\.\d{2})\s+([TF](?:\s+[TF])?)\s*$', line)
        
        # If no match, try Format 2 (no leading spaces, tax before price)
        if not item_match:
            item_match = re.match(r'^(.+?)\s+([TF](?:\s+[TF])?)\s+(-?\d+\.\d{2})\s*$', line)
            if item_match:
                # Reorder groups to match format 1: (name, price, tax)
                item_name = item_match.group(1).strip()
                tax_indicator = item_match.group(2).strip()
                price = float(item_match.group(3))
                # Reconstruct match with standard order
                item_match = type('obj', (object,), {
                    'group': lambda self, n: [None, item_name, str(price), tax_indicator][n]
                })()
        
        if item_match:
            item_name = item_match.group(1).strip()
            price = float(item_match.group(2))
            tax_indicator = item_match.group(3).strip()
            
            # If we should skip negative values (after Voided Item)
            if skip_next_negative and price < 0:
                skip_next_negative = False
                continue
            skip_next_negative = False
            
            # Handle "Voided Item" lines - remove the previous item
            if 'Voided Item' in item_name:
                if current_items:
                    removed_item = current_items.pop()
                    print(f"  ⚠️  Voided: {removed_item['item_name']}")
                # Set flag to skip next line if it has a negative value
                skip_next_negative = True
                continue
            
            # Skip if this looks like a quantity pricing line (e.g., "1 @   2 FOR      6.00")
            if re.match(r'^\d+\s+@', item_name):
                continue
            
            # Skip if this looks like a weight-based pricing line (e.g., "0.64 lb @     2.99/ lb")
            if re.search(r'\d+\.?\d*\s*lb\s*@.*?/ lb', item_name, re.IGNORECASE):
                continue
            
            # Handle "Promotion" and "You Saved" lines
            if 'Promotion' in item_name or 'You Saved' in item_name or 'You saved' in item_name:
                if current_items:
                    # If the previous item has price 0.00, it's likely a BOGO free item
                    # Apply savings to the item before it (the one that was charged)
                    if len(current_items) >= 2 and current_items[-1]['price'] == 0.00:
                        # Mark both items as on sale (BOGO)
                        current_items[-2]['on_sale'] = True
                        current_items[-1]['on_sale'] = True
                        # Split the savings between both items
                        savings_per_item = abs(price) / 2
                        current_items[-2]['promotion_amount'] += savings_per_item
                        current_items[-1]['promotion_amount'] += savings_per_item
                    else:
                        # Normal promotion on single item
                        current_items[-1]['on_sale'] = True
                        # Add the promotion amount as positive (convert negative to positive)
                        # Add to existing promotion_amount in case there are multiple promotion lines
                        current_items[-1]['promotion_amount'] += abs(price)
                continue
            
            # Skip this line if next line is a quantity pricing line - let that handler get it
            if is_qty_line_next:
                continue
            
            # Determine if taxable
            # F = Food/Non-taxable, T = Taxable, "T F" = Taxable Food item
            taxable = 'T' in tax_indicator and tax_indicator != 'F'
            
            item_info = {
                'item_name': item_name,
                'price': price,
                'original_price': price,
                'on_sale': False,  # Will be marked True if next line is Promotion
                'promotion_amount': 0.0,
                'taxable': taxable,
                'tax_marker': tax_indicator,
                'raw_line': line.strip()
            }
            
            current_items.append(item_info)
        
        # Also match lines with quantity pricing: "  1 @   2 FOR      5.00         2.50 T  "
        # Need to get the LAST price (actual amount paid), not the first price (deal price)
        qty_match = re.match(r'^\s+\d+\s+@.*?(\d+\.\d{2})\s+([TF](?:\s+[TF])?)\s*$', line)
        if qty_match and i > 0:
            # Get the item name from the previous non-empty line
            prev_line_text = ""
            for j in range(i-1, -1, -1):
                prev = lines[j].strip()
                if prev and not re.match(r'^\s*\d+\s+@', prev):  # Make sure it's not another qty line
                    # Extract just the item name by removing any trailing price and tax indicators
                    # The line should be in format: "  ITEM NAME" (no price)
                    # If it has a price, extract just the name part
                    item_only = re.sub(r'\s+\d+\.\d{2}\s+[TF].*$', '', prev).strip()
                    prev_line_text = item_only
                    break
            
            if prev_line_text:
                # Extract the actual price paid (the last price on the line before tax indicator)
                # Find all prices on the line, take the last one
                all_prices = re.findall(r'(\d+\.\d{2})', line)
                if all_prices:
                    price = float(all_prices[-1])  # Last price is what was actually paid
                else:
                    price = float(qty_match.group(1))
                
                tax_indicator = qty_match.group(2).strip()
                taxable = 'T' in tax_indicator and tax_indicator != 'F'
                
                item_info = {
                    'item_name': prev_line_text,
                    'price': price,
                    'original_price': price,
                    'on_sale': False,
                    'promotion_amount': 0.0,
                    'taxable': taxable,
                    'tax_marker': tax_indicator,
                    'raw_line': prev_line_text + " | " + line.strip()
                }
                current_items.append(item_info)
    
    return current_items


def parse_receipt_summary(email_body):
    """
    Extract receipt summary information (subtotal, tax, total, etc.)
    
    Args:
        email_body: The email body text
        
    Returns:
        Dictionary with receipt summary
    """
    summary = {
        'subtotal': None,
        'tax': None,
        'total': None,
        'savings': None
    }
    
    lines = email_body.split('\n')
    
    for line in lines:
        line_upper = line.upper()
        
        # Extract subtotal
        if 'SUBTOTAL' in line_upper or 'SUB TOTAL' in line_upper:
            match = re.search(r'\$?\s*(\d+\.\d{2})', line)
            if match:
                summary['subtotal'] = float(match.group(1))
        
        # Extract tax
        if 'TAX' in line_upper and 'TOTAL' not in line_upper and 'TAXABLE' not in line_upper:
            match = re.search(r'\$?\s*(\d+\.\d{2})', line)
            if match:
                summary['tax'] = float(match.group(1))
        
        # Extract total
        if 'TOTAL' in line_upper and 'SUB' not in line_upper:
            match = re.search(r'\$?\s*(\d+\.\d{2})', line)
            if match:
                summary['total'] = float(match.group(1))
        
        # Extract savings
        if 'SAVING' in line_upper or 'YOU SAVED' in line_upper:
            match = re.search(r'\$?\s*(\d+\.\d{2})', line)
            if match:
                summary['savings'] = float(match.group(1))
    
    return summary


def load_credentials():
    """
    Load email credentials from web config file or prompt user.
    Priority:
    1. Web config file (data/config.php) - Set via Settings page
    2. Command-line prompt - Manual entry
    """
    # Try to load from web config file (shared data volume)
    # Check multiple possible locations
    config_paths = [
        '/app/data/config.php',  # Docker volume mount
        os.path.join(os.path.dirname(__file__), 'data', 'config.php'),  # Local data dir
        os.path.join(os.path.dirname(__file__), 'web', 'data', 'config.php')  # Web data dir
    ]
    
    for web_config_file in config_paths:
        if os.path.exists(web_config_file):
            try:
                with open(web_config_file, 'r') as f:
                    content = f.read()
                    # Extract email, password, and provider from PHP defines
                    email_match = re.search(r"define\('GMAIL_EMAIL',\s*'([^']+)'\)", content)
                    password_match = re.search(r"define\('GMAIL_PASSWORD',\s*'([^']+)'\)", content)
                    provider_match = re.search(r"define\('EMAIL_PROVIDER',\s*'([^']+)'\)", content)
                    if email_match and password_match:
                        provider = provider_match.group(1) if provider_match else None
                        print(f"✓ Loaded credentials from {web_config_file}")
                        return email_match.group(1), password_match.group(1), provider
            except Exception as e:
                print(f"Warning: Could not read config from {web_config_file}: {e}")
    
    # Prompt user for credentials
    print("No saved credentials found. Please configure credentials in the web Settings page,")
    print("or enter them manually below.\n")
    email_address = input("Enter your email address: ")
    password = getpass("Enter your email app password: ")
    return email_address, password, None


def main():
    """
    Main function to retrieve Publix receipts from Gmail.
    """
    print("=== Publix Receipt Retriever ===\n")
    
    # Initialize database - use data directory path
    db_path = "/app/data/publix_tracker.db"
    db_conn = init_database(db_path)
    
    # Get Gmail credentials
    # Note: For Gmail, you need to use an App Password, not your regular password
    # Create one at: https://myaccount.google.com/apppasswords
    email_address, password, provider = load_credentials()
    print(f"Using email: {email_address}\n")
    
    # Connect to email provider
    imap = connect_to_gmail(email_address, password, provider)
    
    if not imap:
        print("Failed to connect to Gmail")
        db_conn.close()
        return
    
    # Store db_conn in a way it can be accessed in the loop
    imap_conn = db_conn
    
    try:
        # Get all Publix receipt emails
        emails = get_publix_receipts(imap, None, None)
        
        print(f"\n=== Retrieved {len(emails)} emails ===\n")
        
        # Create the processed folder
        folder_name = "Publix Processed"
        create_gmail_folder(imap, folder_name)
        
        # Process each email
        processed_count = 0
        skipped_count = 0
        
        for idx, email_info in enumerate(emails, 1):
            print(f"\n--- Email {idx}/{len(emails)} ---")
            print(f"From: {email_info['from']}")
            print(f"Subject: {email_info['subject']}")
            print(f"Date: {email_info['date']}")
            
            # Check if already processed
            if is_receipt_processed(imap_conn, email_info['id']):
                print("⏭️  SKIPPED: Receipt already processed (found in database)")
                skipped_count += 1
                continue
            
            # Parse receipt items
            items = parse_receipt_items(email_info['body'])
            summary = parse_receipt_summary(email_info['body'])
            
            print(f"\n📦 Items Purchased: {len(items)}")
            print("-" * 70)
            
            # Extract purchase date from email date
            purchase_date = email_info['date']
            try:
                # Parse various date formats
                from email.utils import parsedate_to_datetime
                purchase_datetime = parsedate_to_datetime(purchase_date)
                purchase_date_str = purchase_datetime.strftime('%Y-%m-%d')
            except:
                # Fallback to current date if parsing fails
                purchase_date_str = datetime.now().strftime('%Y-%m-%d')
            
            for item in items:
                tax_status = "🟢 Taxable" if item['taxable'] else "🔵 Non-Taxable" if item['taxable'] is False else "❓ Unknown"
                sale_status = "💰 ON SALE" if item['on_sale'] else ""
                
                # If on sale, store price as $0.00 in database
                db_price = 0.00 if item['on_sale'] else item['price']
                
                # Get individual item savings (promotion amount)
                item_savings = item.get('promotion_amount', 0.0)
                
                print(f"  • {item['item_name']:<40} ${item['price']:>6.2f}  {tax_status}  {sale_status}")
                if item_savings > 0:
                    print(f"    Saved: ${item_savings:.2f}")
                
                # Insert into database with individual item savings
                insert_purchase(
                    conn=imap_conn,
                    purchase_date=purchase_date_str,
                    item_name=item['item_name'],
                    price=db_price,
                    on_sale=item['on_sale'],
                    taxable=item['taxable'],
                    email_id=email_info['id'],
                    savings=item_savings
                )
            
            print("\n" + "=" * 70)
            print("📊 Receipt Summary:")
            if summary['subtotal']:
                print(f"  Subtotal: ${summary['subtotal']:.2f}")
            if summary['tax']:
                print(f"  Tax:      ${summary['tax']:.2f}")
            if summary['total']:
                print(f"  Total:    ${summary['total']:.2f}")
            if summary['savings']:
                print(f"  Savings:  ${summary['savings']:.2f}")
            print("=" * 70)
            
            # Optionally save attachments
            save_attachments(email_info['message'], email_info['id'])
            
            # Move email to processed folder
            print(f"\n📧 Moving email to '{folder_name}' folder...")
            # Need to reselect INBOX before moving
            imap.select("INBOX")
            if move_email_to_folder(imap, email_info['id'].encode(), folder_name):
                print(f"✓ Email moved successfully")
                processed_count += 1
            else:
                print(f"✗ Failed to move email")
        
        # Print summary
        print("\n" + "=" * 70)
        print("📊 Processing Summary:")
        print(f"  Total emails found: {len(emails)}")
        print(f"  ✓ Processed: {processed_count}")
        print(f"  ⏭️  Skipped (duplicates): {skipped_count}")
        print("=" * 70)
        
    finally:
        # Close the connections
        imap.close()
        imap.logout()
        db_conn.close()
        print("\n\nDisconnected from Gmail")
        print("Database connection closed")


if __name__ == "__main__":
    main()
