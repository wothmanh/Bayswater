import os
import sys

# Set environment variables
os.environ["MCP_LOG_LEVEL"] = "INFO"

# Run the calculator server
from mcp_server_calculator.calculator import main
main()
