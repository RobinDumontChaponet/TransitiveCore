# Change Log
All notable changes to this project will be documented in this file.

The format is (try to) based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## 2.2.0
Namespaces have been refactored again…

### Now : 
- Transitive\Core are base classes (package transitive/core)
- Transitive\Simple are basic view and front classes (package transitive/core)
- Transitive\Routing are routing classes (package transitive/routing)
- Transitive\Web are web view and front classes (package transitive/web)

### Coming later :
- Transitive\BulletinBoardSystem
- Transitive\CommandLine

## 2.1.1
### fixed
- package name

## 2.1.0
Transitive\Core is now more "modular", routing and associated functions have been moved to Transitive\Front

### changed
- fix typo in BasicFront. Route could not be found otherwise.

### removed
- routing and associated functions : routers and fronts
