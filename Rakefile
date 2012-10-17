task :regen_wsdl do
	environments = {
		:test 	     => "http://api.test.scoteid.com/api",
		:production  => "http://api.scoteid.com/api",
		:staging     => "http://api.staging.scoteid.com/api"
	}
	environments.keys.each do |environment|
		f = File.read(File.dirname(__FILE__) + "/api.wsdl")
		f.gsub!("http://localhost:3000/api.php", environments[environment])
		File.open(File.dirname(__FILE__) + "/api-#{environment}.wsdl", "w") do |file|
			file.puts(f)
		end
	end
end

task :test do
  system("phpunit test/unit")
  system("phpunit test/requests");
end
