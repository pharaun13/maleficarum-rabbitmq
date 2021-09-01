# Change Log
This is the Maleficarum RabbitMQ component implementation. 

## [12.1.1] - 2021-09-01
### Added
- Automatic injection of context tracking headers
- Dependency to context tracking library 

## [12.0.0] - 2021-01-20
### Changed
- Bump command component to version 5.0

## [11.0.0] - 2020-06-05
### Changed
- Bump command component to version 4.0

## [10.2.0] - 2020-05-22
### Added
- Added support for sending amqp messages with headers

## [10.1.0] - 2020-03-19
### Added
- Added an option to gracefully remove a connection object from the connection manager.

## [10.0.0] - 2020-02-18
### Added
- bump php-amqplib version to 2.11

## [9.3.0] - 2019-10-03
### Added
- added exchange name for addCommand|addCommands|addRawMessage methods

## [9.2.0] - 2019-09-02
### Added
- added support for testMode command parameter

## [9.1.0] - 2018-09-14
### Changed
- When attempting to create a connection there will be a double retry from now on. (Exactly 3 attempts will be made to establish the connection).
- Increased connection timeout parameters from 3s to 10s.

## [9.0.0] - 2018-09-12
### Changed
- Updated component to work with Maleficarum\Ioc 3.X and Maleficarum\Command 3.X.
- Bumped PHP version requirement to 7.2+.

## [8.2.0] - 2018-09-14
### Changed
- When attempting to create a connection there will be a double retry from now on. (Exactly 3 attempts will be made to establish the connection).
- Increased connection timeout parameters from 3s to 10s.

## [8.1.0] - 2018-04-09
### Added
- Added the possibility to define connection vhost setting via the connection constructor.

## [8.0.1] - 2018-03-29
### Fixed
- Incorrect validation for port value when building a new connection object.
- Added a missing invocation that adds the manager object as a default Maleficarum command router in the default initializer logic. 

## [8.0.0] - 2018-03-29
### Changed
- Added a connection manager class - it should be used to access rabbit mq connections in a seemless way. Direct access to the connection is still available but should be avoided.
- Added support for connection sources with defined priority.
- Added support for transient connections - automatically closed after each use. Transient connections cannot be used as command sources.

## [7.1.0] - 2018-09-14
### Changed
- When attempting to create a connection there will be a double retry from now on. (Exactly 3 attempts will be made to establish the connection).
- Increased connection timeout parameters from 3s to 10s.

## [7.0.0] - 2017-08-03
### Changed
- Make use of nullable types provided in PHP 7.1 (http://php.net/manual/en/migration71.new-features.php)
- Fix tests

## [6.0.3] - 2017-05-10
### Fixed
- Cast port to integer

## [6.0.2] - 2017-04-06
### Fixed
- Cast port to integer

## [6.0.1] - 2017-04-06
### Fixed
- Move delcare before namespace delcaration

## [6.0.0] - 2017-03-24
### Changed
- Changed internal structure.
- Added default package initializer.

## [5.0.2] - 2017-03-08
### Fixed
- Fix addRawMessage method by passing AMQPMessage object instead of string

## [5.0.1] - 2017-03-08
### Fixed
- Replace deprecated AMQPConnection with AMQPStreamConnection

## [5.0.0] - 2017-03-08
### Added
- Add connection parameters to constructor
- Fix tests

## [4.1.0] - 2017-03-07
### Added
- Add method for raw message push

## [4.0.0] - 2017-03-01
### Changed
- Remove config component
- Fix tests

## [3.0.1] - 2017-02-15
### Added
- Add tests

## [3.0.0] - 2017-01-30
### Changed
- Changed namespace of command component

## [2.0.0] - 2017-01-23
### Changed
- Add return and argument types declaration

## [1.0.0] - 2017-01-09
### Added
- This was an initial release based on the code written by pharaun13 and added to the repo by me
