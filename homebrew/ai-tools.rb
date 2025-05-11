class AiTools < Formula
  desc "Generate AI-friendly project overviews"
  homepage "https://github.com/duanestorey/ai-tools"
  url "https://github.com/duanestorey/ai-tools/archive/refs/tags/v1.0.0.tar.gz"
  sha256 "REPLACE_WITH_ACTUAL_SHA256_AFTER_RELEASE"
  license "MIT"
  
  depends_on "php"
  depends_on "composer"
  
  def install
    # Install composer dependencies
    system "composer", "install", "--no-dev", "--prefer-dist"
    
    # Create the libexec directory to store the package files
    libexec.install Dir["*"]
    
    # Create a wrapper script for the ai-overview command
    (bin/"ai-overview").write <<~EOS
      #!/bin/bash
      PHP_BIN=$(which php)
      SCRIPT_PATH=#{libexec}/bin/ai-overview
      $PHP_BIN "$SCRIPT_PATH" "$@"
    EOS
    chmod 0755, bin/"ai-overview"
    
    # Create a wrapper script for easier usage with ai-tools name
    (bin/"ai-tools").write <<~EOS
      #!/bin/bash
      PHP_BIN=$(which php)
      SCRIPT_PATH=#{libexec}/bin/ai-overview
      $PHP_BIN "$SCRIPT_PATH" "$@"
    EOS
    chmod 0755, bin/"ai-tools"
  end
  
  test do
    system "#{bin}/ai-tools", "--version"
  end
end
