require 'rake/clean'
require 'rake/contrib/sys'

SRC = FileList['lib/**/*']
CLEAN.include('doc')

desc 'Create documentation'
file 'doc' => SRC do
  sh "phpdoc --sourcecode on -t `pwd`/doc -d `pwd`/lib -ti 'trails documentation' -o 'HTML:frames:earthli'"
end

desc 'Run all unit tests'
task 'test' do
  sh "php test/all_tests.php"
end

desc 'Run coverage'
task 'coverage' do
  Sys.indir "test" do
    sh "php coverage.php"
  end
end
