# Component based object

A simple starter class for component based objects.

This is used when flexibility is needed, for highly interactive components.

## Dependency solver system.

From version 3, you have a new dependency solver system. It makes instantiation
easier.

You may declare dependencies by extending `getDependencyDefinitions()`.

Using the class constructor and the `new` keyword is highly discouraged to
prevent logical problems on instantiation.
Always use the methods to add or retrieve components.

You can set two types of dependencies:

- root: easily acccessed using static methods by any object.
- fellow: accessed by components on the same "level" (siblings).
  Rarely useful. Used for interdependent components or components without
  special relationships (root components are all fellows).

You can create sub-components, but they cannot be added during the
dependency solving step. They must be added after the instance is ready.

```php
<?php

use Dakwamine\Component\ComponentBasedObject;
use Dakwamine\Component\DependencyDefinition;

class SomeObject extends ComponentBasedObject
{
  public function getDependencyDefinitions(): array
  {
    $deps = parent::getDependencyDefinitions();

    // Example of a mandatory fellow dependency.
    // Throws an error on dependency processing
    // if no suitable dependency has been found.
    $dependencyA = new FellowDependencyDefinition('A\Class\Name');
    $dependencyA
      ->setBackupDependency(new FellowDependencyDefinition('Backup\Class\Name'));

    // Example of a fellow dependency, which transforms to root
    // dependency as a backup method.
    // You can chain backups.
    $dependencyB = new FellowDependencyDefinition('B\Class\Name');
    $dependencyB
      ->setBackupDependency(new RootDependencyDefinition('Missing\Class\Name'))
      ->setBackupDependency(new RootDependencyDefinition(AnotherComponentBasedObject::class));

    $deps[] = $dependencyA;
    $deps[] = $dependencyB;

    return $deps;
  }
}
```

## Implementation example

We have a **LandManager** which holds references to locations, a
**Company** and **Home** objects.

A **Company** has **Team**s.
A **Team** has **Member**s.
A **Member** has means of **Transport** and a **Home**.

The **Company** needs a **Clock** to know when to send **Member**s to work.

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

class LandManager extends ComponentBasedObject {}

class Clock extends ComponentBasedObject
{
  public function isTimeToWork(): bool
  {
    // It's always time to work, sadly...
    return true;
  }
}

abstract class Location extends ComponentBasedObject {}

class Home extends Location {}

class Company extends Location {
  public function getDependencyDefinitions(): array
  {
    $deps = parent::getDependencyDefinitions();

    // A company needs a general/worldwide clock.
    // If the Clock class does not exist, when instantiating
    // this Company, this will throw an exception.
    $deps[] = new RootDependencyDefinition(Clock::class);

    return $deps;
  }

  public function updateTeams(): void
  {
    $isTimeToWork = ComponentBasedObject::getRootComponentByClassName(Clock::class)->isTimeToWork();

    foreach($this->getComponentsByClassName(Team::class) as $team) {
      if ($isTimeToWork) {
        $team->sendToWork();
        continue;
      }
      $team->sendHome();
    }
  }
}

class Team extends ComponentBasedObject
{
  public function buildTeam()
  {
    foreach ($this->getStartingTeamMembersInfo() as $info) {
      $member = $this->addComponentByClassName(Member::class);
      $member->setInfo($info);
    }
  }

  protected function getStartingTeamMembersInfo(): array
  {
    // Empty base team.
    return [];
  }

  public function sendToWork(): void
  {
    foreach($this->getComponentsByClassName(Member::class) as $member) {
      $member->goToWork();
    }
  }

  public function sendHome(): void
  {
    foreach($this->getComponentsByClassName(Member::class) as $member) {
      $member->goHome();
    }
  }
}

class ManagementTeam extends Team {
  protected function getStartingTeamMembersInfo(): array
  {
    return [
      [
        'name' => 'Fu',
        'equipment' => 'Car',
      ],
      [
        'name' => 'Bart',
        'equipment' => 'Bus',
      ],
    ];
  }
}

class DevTeam extends Team {
  protected function getStartingTeamMembersInfo(): array
  {
    return [
      [
        'name' => 'Jane',
        'equipment' => 'Helicopter',
      ],
      [
        'name' => 'Saitama',
        'equipment' => 'Legs',
      ],
    ];
  }
}

class Member extends ComponentBasedObject
{
  private $info;

  private $currentLocation;

  /**
   * Member home.
   *
   * @var Home
   */
  private $home;

  /**
   * Current company.
   *
   * @var Company
   */
  private $company;

  private $atWork, $atHome;

  public function setInfo($info): void
  {
    $this->info = $info;
  }

  public function goToWork(): void
  {
    if (empty($this->company)) {
      return;
    }

    if ($this->atWork) {
      return;
    }

    $transport = $this->getMeansOfTransport();

    if (empty($transport)) {
      return;
    }

    $transport->move([$this], $this->company);
    $this->atWork = true;
    $this->atHome = false;
  }

  public function goHome(): void
  {
    if ($this->atHome) {
      return;
    }

    $transport = $this->getMeansOfTransport();

    if (empty($transport)) {
      return;
    }

    if (empty($home = $this->getHome())) {
      // LandManager refused to give this member a home.
      return;
    }

    $transport->move([$this], $home);
    $this->atHome = true;
    $this->atWork = false;
  }

  public function getMeansOfTransport(): ?Transport
  {
    if (empty($this->info['equipment'])) {
      return null;
    }

    // The equipment is owned by this member only. A sub-component.
    return $this->getComponentByClassName($this->info['equipment']);
  }

  public function getHome(): ?Home
  {
    if (empty($this->home)) {
      // Ask the land manager to give a home.
      $landManager = static::getRootComponentByClassName(LandManager::class);

      $this->home = $landManager->addComponentByClassName(Home::class);
    }

    return $this->home;
  }

  public function getName(): string
  {
    if (empty($this->info['name'])) {
      return '';
    }

    return $this->info['name'];
  }

  public function setLocation(Location $location): void
  {
    $this->currentLocation = $location;
  }
}

/**
 * Extending ComponentBasedObject to use it as a component is not mandatory.
 * We can use normal classes if we don't need dependency checks and
 * ComponentBasedObject methods.
 */
abstract class Transport {
  public function move($member, Location $destination): void
  {
    // For this example, all means of transport are instantaneous.
    // A more complete example would interpolate positions or use GPS and use
    // the Transport speed.
    $member->setLocation($destination);
  }
}
class Car extends Transport {}
class Train extends Transport {}
class Helicopter extends Transport {}
class Legs extends Transport {}
```

```php
// We make our land manager a root component.
$landManager = ComponentBasedObject::getRootComponentByClassName(LandManager::class);

// Create a company registered by a land manager.
// If Clock does not exist, this will throw an exception here.
$company = $landManager->addComponentByClassName(Company::class);

// Add two teams to the company.
$managementTeam = $company->addComponentByClassName(ManagementTeam::class);
$managementTeam->buildTeam();
$devTeam = $company->addComponentByClassName(DevTeam::class);
$devTeam->buildTeam();

// Make the teams go to work, or not (depends on the Clock).
$company->updateTeams();
```
