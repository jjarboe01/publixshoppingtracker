import sqlite3
from datetime import datetime, timedelta
from collections import defaultdict
import calendar


def connect_database(db_path="publix_tracker.db"):
    """
    Connect to the SQLite database.
    
    Args:
        db_path: Path to the database file
        
    Returns:
        Database connection object
    """
    try:
        conn = sqlite3.connect(db_path)
        return conn
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return None


def view_by_date(conn, date_str):
    """
    View all purchases for a specific date.
    
    Args:
        conn: Database connection
        date_str: Date string in YYYY-MM-DD format
    """
    cursor = conn.cursor()
    
    cursor.execute('''
        SELECT item_name, price, on_sale, taxable
        FROM purchases
        WHERE purchase_date = ?
        ORDER BY item_name
    ''', (date_str,))
    
    items = cursor.fetchall()
    
    if not items:
        print(f"\nNo purchases found for {date_str}")
        return
    
    print(f"\n{'='*80}")
    print(f"📅 Purchases on {date_str}")
    print(f"{'='*80}\n")
    
    total = 0
    sale_items = 0
    
    for item_name, price, on_sale, taxable in items:
        sale_indicator = "💰 SALE" if on_sale else ""
        tax_indicator = "🟢 T" if taxable else "🔵 F"
        
        display_price = price if not on_sale else 0.00
        print(f"  • {item_name:<45} ${display_price:>7.2f}  {tax_indicator}  {sale_indicator}")
        
        if not on_sale:
            total += price
        else:
            sale_items += 1
    
    print(f"\n{'='*80}")
    print(f"  Total Items: {len(items)}")
    print(f"  Sale Items: {sale_items}")
    print(f"  Paid Total: ${total:.2f}")
    print(f"{'='*80}\n")


def view_date_range(conn, start_date, end_date):
    """
    View all purchases within a date range.
    
    Args:
        conn: Database connection
        start_date: Start date string in YYYY-MM-DD format
        end_date: End date string in YYYY-MM-DD format
    """
    cursor = conn.cursor()
    
    cursor.execute('''
        SELECT purchase_date, item_name, price, on_sale, taxable
        FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
        ORDER BY purchase_date, item_name
    ''', (start_date, end_date))
    
    items = cursor.fetchall()
    
    if not items:
        print(f"\nNo purchases found between {start_date} and {end_date}")
        return
    
    print(f"\n{'='*80}")
    print(f"📅 Purchases from {start_date} to {end_date}")
    print(f"{'='*80}\n")
    
    by_date = defaultdict(list)
    
    for purchase_date, item_name, price, on_sale, taxable in items:
        by_date[purchase_date].append((item_name, price, on_sale, taxable))
    
    grand_total = 0
    total_items = 0
    total_sale_items = 0
    
    for date in sorted(by_date.keys()):
        print(f"\n{date}:")
        date_total = 0
        
        for item_name, price, on_sale, taxable in by_date[date]:
            sale_indicator = "💰" if on_sale else ""
            tax_indicator = "🟢" if taxable else "🔵"
            
            display_price = price if not on_sale else 0.00
            print(f"  • {item_name:<40} ${display_price:>7.2f}  {tax_indicator} {sale_indicator}")
            
            if not on_sale:
                date_total += price
            else:
                total_sale_items += 1
            
            total_items += 1
        
        print(f"  {'─'*50}")
        print(f"  Date Total: ${date_total:.2f}")
        grand_total += date_total
    
    print(f"\n{'='*80}")
    print(f"  Total Items: {total_items}")
    print(f"  Sale Items: {total_sale_items}")
    print(f"  Grand Total: ${grand_total:.2f}")
    print(f"{'='*80}\n")


def monthly_summary(conn, year, month):
    """
    View monthly summary of purchases.
    
    Args:
        conn: Database connection
        year: Year (e.g., 2026)
        month: Month (1-12)
    """
    cursor = conn.cursor()
    
    # Get start and end dates for the month
    start_date = f"{year}-{month:02d}-01"
    last_day = calendar.monthrange(year, month)[1]
    end_date = f"{year}-{month:02d}-{last_day}"
    
    month_name = calendar.month_name[month]
    
    # Get all items for the month
    cursor.execute('''
        SELECT item_name, SUM(price) as total_price, COUNT(*) as count, 
               SUM(CASE WHEN on_sale = 1 THEN 1 ELSE 0 END) as sale_count
        FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
        GROUP BY item_name
        ORDER BY total_price DESC
    ''', (start_date, end_date))
    
    items = cursor.fetchall()
    
    if not items:
        print(f"\nNo purchases found for {month_name} {year}")
        return
    
    print(f"\n{'='*80}")
    print(f"📊 Monthly Summary: {month_name} {year}")
    print(f"{'='*80}\n")
    
    print(f"{'Item':<40} {'Times':<8} {'Total':<12} {'Avg Price':<12} {'Sale'}")
    print(f"{'-'*80}")
    
    grand_total = 0
    total_items = 0
    total_purchases = 0
    
    for item_name, total_price, count, sale_count in items:
        avg_price = total_price / count if count > 0 else 0
        sale_indicator = f"{sale_count}x 💰" if sale_count > 0 else ""
        
        print(f"{item_name:<40} {count:<8} ${total_price:<11.2f} ${avg_price:<11.2f} {sale_indicator}")
        
        grand_total += total_price
        total_items += 1
        total_purchases += count
    
    # Get shopping trips
    cursor.execute('''
        SELECT COUNT(DISTINCT purchase_date) as trips
        FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
    ''', (start_date, end_date))
    
    trips = cursor.fetchone()[0]
    
    print(f"\n{'='*80}")
    print(f"  Unique Items: {total_items}")
    print(f"  Total Purchases: {total_purchases}")
    print(f"  Shopping Trips: {trips}")
    print(f"  Monthly Total: ${grand_total:.2f}")
    if trips > 0:
        print(f"  Average per Trip: ${grand_total/trips:.2f}")
    print(f"{'='*80}\n")


def quarterly_summary(conn, year, quarter):
    """
    View quarterly summary of purchases.
    
    Args:
        conn: Database connection
        year: Year (e.g., 2026)
        quarter: Quarter (1-4)
    """
    cursor = conn.cursor()
    
    # Determine start and end months
    start_month = (quarter - 1) * 3 + 1
    end_month = start_month + 2
    
    start_date = f"{year}-{start_month:02d}-01"
    last_day = calendar.monthrange(year, end_month)[1]
    end_date = f"{year}-{end_month:02d}-{last_day}"
    
    quarter_months = [calendar.month_name[m] for m in range(start_month, end_month + 1)]
    
    # Get all items for the quarter
    cursor.execute('''
        SELECT item_name, SUM(price) as total_price, COUNT(*) as count,
               SUM(CASE WHEN on_sale = 1 THEN 1 ELSE 0 END) as sale_count
        FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
        GROUP BY item_name
        ORDER BY total_price DESC
    ''', (start_date, end_date))
    
    items = cursor.fetchall()
    
    if not items:
        print(f"\nNo purchases found for Q{quarter} {year}")
        return
    
    print(f"\n{'='*80}")
    print(f"📊 Quarterly Summary: Q{quarter} {year} ({', '.join(quarter_months)})")
    print(f"{'='*80}\n")
    
    print(f"{'Item':<40} {'Times':<8} {'Total':<12} {'Avg Price':<12} {'Sale'}")
    print(f"{'-'*80}")
    
    grand_total = 0
    total_items = 0
    total_purchases = 0
    
    for item_name, total_price, count, sale_count in items:
        avg_price = total_price / count if count > 0 else 0
        sale_indicator = f"{sale_count}x 💰" if sale_count > 0 else ""
        
        print(f"{item_name:<40} {count:<8} ${total_price:<11.2f} ${avg_price:<11.2f} {sale_indicator}")
        
        grand_total += total_price
        total_items += 1
        total_purchases += count
    
    # Get monthly breakdown
    print(f"\n{'─'*80}")
    print(f"Monthly Breakdown:")
    print(f"{'─'*80}\n")
    
    for m in range(start_month, end_month + 1):
        month_start = f"{year}-{m:02d}-01"
        last_day = calendar.monthrange(year, m)[1]
        month_end = f"{year}-{m:02d}-{last_day}"
        
        cursor.execute('''
            SELECT SUM(price), COUNT(DISTINCT purchase_date)
            FROM purchases
            WHERE purchase_date BETWEEN ? AND ?
        ''', (month_start, month_end))
        
        month_data = cursor.fetchone()
        month_total = month_data[0] if month_data[0] else 0
        month_trips = month_data[1] if month_data[1] else 0
        
        print(f"  {calendar.month_name[m]:<15} ${month_total:>10.2f}  ({month_trips} trips)")
    
    # Get shopping trips
    cursor.execute('''
        SELECT COUNT(DISTINCT purchase_date) as trips
        FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
    ''', (start_date, end_date))
    
    trips = cursor.fetchone()[0]
    
    print(f"\n{'='*80}")
    print(f"  Unique Items: {total_items}")
    print(f"  Total Purchases: {total_purchases}")
    print(f"  Shopping Trips: {trips}")
    print(f"  Quarterly Total: ${grand_total:.2f}")
    if trips > 0:
        print(f"  Average per Trip: ${grand_total/trips:.2f}")
    print(f"{'='*80}\n")


def view_all_dates(conn):
    """
    View all unique purchase dates in the database.
    
    Args:
        conn: Database connection
    """
    cursor = conn.cursor()
    
    cursor.execute('''
        SELECT DISTINCT purchase_date, COUNT(*) as item_count, SUM(price) as total
        FROM purchases
        GROUP BY purchase_date
        ORDER BY purchase_date DESC
    ''')
    
    dates = cursor.fetchall()
    
    if not dates:
        print("\nNo purchases found in database")
        return
    
    print(f"\n{'='*80}")
    print(f"📅 All Shopping Dates")
    print(f"{'='*80}\n")
    print(f"{'Date':<15} {'Items':<10} {'Total':<12}")
    print(f"{'-'*80}")
    
    for purchase_date, item_count, total in dates:
        print(f"{purchase_date:<15} {item_count:<10} ${total:<11.2f}")
    
    print(f"{'='*80}\n")


def top_items(conn, limit=20):
    """
    View top purchased items.
    
    Args:
        conn: Database connection
        limit: Number of top items to show
    """
    cursor = conn.cursor()
    
    cursor.execute('''
        SELECT 
            p.item_name, 
            COUNT(*) as purchase_count, 
            SUM(p.price) as total_spent,
            AVG(p.price) as avg_price,
            MIN(p.price) as min_price,
            MAX(p.price) as max_price,
            (SELECT price FROM purchases 
             WHERE item_name = p.item_name 
             ORDER BY purchase_date DESC, created_at DESC LIMIT 1) as last_price,
            (SELECT purchase_date FROM purchases 
             WHERE item_name = p.item_name 
             ORDER BY purchase_date DESC, created_at DESC LIMIT 1) as last_date
        FROM purchases p
        GROUP BY p.item_name
        ORDER BY purchase_count DESC
        LIMIT ?
    ''', (limit,))
    
    items = cursor.fetchall()
    
    if not items:
        print("\nNo items found in database")
        return
    
    print(f"\n{'='*115}")
    print(f"🏆 Top {limit} Most Purchased Items")
    print(f"{'='*115}\n")
    print(f"{'Item':<40} {'Count':<8} {'Total':<10} {'Avg':<10} {'Low':<10} {'High':<10} {'Last Price':<12} {'Last Date':<12}")
    print(f"{'-'*115}")
    
    for item_name, count, total, avg, min_price, max_price, last_price, last_date in items:
        print(f"{item_name:<40} {count:<8} ${total:<9.2f} ${avg:<9.2f} ${min_price:<9.2f} ${max_price:<9.2f} ${last_price:<11.2f} {last_date:<12}")
    
    print(f"{'='*115}\n")


def interactive_menu():
    """
    Interactive menu for viewing database.
    """
    conn = connect_database()
    
    if not conn:
        print("Failed to connect to database")
        return
    
    while True:
        print("\n" + "="*80)
        print("📊 Publix Purchase Tracker - Database Viewer")
        print("="*80)
        print("\n1. View purchases by date")
        print("2. View purchases by date range")
        print("3. Monthly summary")
        print("4. Quarterly summary")
        print("5. View all shopping dates")
        print("6. Top purchased items")
        print("7. Exit")
        
        choice = input("\nEnter your choice (1-7): ").strip()
        
        if choice == "1":
            date_str = input("Enter date (YYYY-MM-DD): ").strip()
            try:
                datetime.strptime(date_str, '%Y-%m-%d')
                view_by_date(conn, date_str)
            except ValueError:
                print("Invalid date format. Please use YYYY-MM-DD")
        
        elif choice == "2":
            start_date = input("Enter start date (YYYY-MM-DD): ").strip()
            end_date = input("Enter end date (YYYY-MM-DD): ").strip()
            try:
                datetime.strptime(start_date, '%Y-%m-%d')
                datetime.strptime(end_date, '%Y-%m-%d')
                view_date_range(conn, start_date, end_date)
            except ValueError:
                print("Invalid date format. Please use YYYY-MM-DD")
        
        elif choice == "3":
            year = input("Enter year (e.g., 2026): ").strip()
            month = input("Enter month (1-12): ").strip()
            try:
                year = int(year)
                month = int(month)
                if 1 <= month <= 12:
                    monthly_summary(conn, year, month)
                else:
                    print("Month must be between 1 and 12")
            except ValueError:
                print("Invalid input. Please enter valid numbers")
        
        elif choice == "4":
            year = input("Enter year (e.g., 2026): ").strip()
            quarter = input("Enter quarter (1-4): ").strip()
            try:
                year = int(year)
                quarter = int(quarter)
                if 1 <= quarter <= 4:
                    quarterly_summary(conn, year, quarter)
                else:
                    print("Quarter must be between 1 and 4")
            except ValueError:
                print("Invalid input. Please enter valid numbers")
        
        elif choice == "5":
            view_all_dates(conn)
        
        elif choice == "6":
            limit_input = input("Enter number of items to show (default 20): ").strip()
            try:
                limit = int(limit_input) if limit_input else 20
                top_items(conn, limit)
            except ValueError:
                print("Invalid input. Using default (20)")
                top_items(conn, 20)
        
        elif choice == "7":
            print("\nGoodbye! 👋")
            break
        
        else:
            print("Invalid choice. Please enter 1-7")
    
    conn.close()


if __name__ == "__main__":
    interactive_menu()
