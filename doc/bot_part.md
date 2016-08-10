# Bot part of PrestonBot

## Labelization system

PrestonBot have automatic and semi-automatic labelization capacities.

### Semi-automatic Labelization

If you add a comment with certains "patterns", PrestonBot will automatically 
add a label to the pull request:

| Pattern to put in a comment  | Label added
|------------------------------|------------------
| ``Status: 'QA approved'``    | ``QA-approved``
| ``Status: 'PM approved'``    | ``PM-approved``
| ``Status: 'Code reviewed'``  | ``Code reviewed``

> This feature is usable for everyone, if required we will restrict the feature
only to PrestaShop team.

### Automatic Labelization

> This is not available for now (23/07/2016)

On pull request creation, PrestonBot will automatically add a ``to be reviewed``
label. Why ? Because this allow us to do some statistics more easily. Also,
the dev core team will be able to receive everyday the 5 to 10 lasts pull requests
contributed.

