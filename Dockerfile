# Use official Python image as base
FROM python:3.11-slim

# Install system dependencies
RUN apt-get update && apt-get install -y \
    gcc \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy Python files
COPY GetReciepts.py ViewDatabase.py ./
COPY requirements.txt* ./

# Install Python dependencies
RUN pip install --no-cache-dir imaplib2 || pip install --no-cache-dir email || true

# Create directories
RUN mkdir -p /app/receipts /app/data

# Set environment variables
ENV PYTHONUNBUFFERED=1

# Default command
CMD ["python3", "GetReciepts.py"]
