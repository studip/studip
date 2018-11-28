#!/usr/bin/env ruby
# client.rb - Stud.IP SOAP client
#
# Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as
# published by the Free Software Foundation; either version 2 of
# the License, or (at your option) any later version.


WSDL_URL = "http://pomona/studip/mlunzena/trunk/webservices/soap.php?wsdl"


### w/ WSDL ###
require 'soap/wsdlDriver'
soap = SOAP::WSDLDriverFactory.new(WSDL_URL).create_rpc_driver


begin
puts "Creating Karl May."
user = soap.create_user('secret',
                        {:user_name  => 'kmay',
                         :first_name => 'Karl',
                         :last_name  => 'May',
                         :email      => 'marcus.lunzenauer@uos.de',
                         :permission => 'user'})
puts "  success: #{user}";
rescue
  puts "create_user: error\n#{soap}"
end

begin
puts "Retrieving Karl May."
karl = soap.find_user_by_user_name('secret', 'kmay')
puts "  " + karl.inspect
rescue
  puts "find_user_by_user_name: error\n#{soap}"
end

puts "Updating Karl May."
karl.email = "karlmay@googlemail.com"
updated = soap.update_user('secret', karl)
puts "  " + (updated ? "success" : "fault");

begin
puts "Deleting Karl May."
result = soap.delete_user('secret', 'kmay')
puts "  success: #{result}";
rescue
  puts "delete_user: error\n#{soap}"
end
