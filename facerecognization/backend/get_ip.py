import socket

def print_ip():
    try:
        # Create a dummy socket to find the primary network interface
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        
        print("\n" + "="*50)
        print("           YOUR COMPUTER'S NETWORK IP")
        print("="*50)
        print(f"  👉  http://{ip}:8000")
        print("="*50)
        print("\nType the URL above into the 'Backend Host URL' field on your phone screen!\n")
    except Exception as e:
        print(f"Error determining local IP: {e}")

if __name__ == "__main__":
    print_ip()
