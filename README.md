### Thoughts about the code

### What is good about the code?
The code is useful because it employs the repository pattern. The repository pattern gives us more control over the code and makes it more resistant to changes. For example, if we want to replace one package with another, we can do so without much refactoring of the code. We can also use the repository pattern to avoid code duplication, thus adhering to the DRY principle, by keeping common code in one place and reusing it as needed.

### What is bad about the code

### Refactoring

While refactoring I considered following points
- Follow DRY principles
- Use proper type hinting
- Use proper typed return type for methods
- Readable code
- Dedicated methods
- Easy to understand naming
- Close to english.

_NOTE: Refactoring is based on PHP 8.1_

1. DTApi/Http/Controllers/BookingController.php
    - Added proper type hinted properties and methods return types
    - Fixed use of potentially undefined variables.
    - Removed unused variables.
    - Moved some repeated code to a common method getDataAndAuthUser()
    - Used inline simple expressions instead of storing them in variables. Will improve memory usage.
    - Fixed Array index is immediately overwritten before accessing
    - Fixed missing returns and return types of methods.

2. DTApi/Repository/BookingRepository.php
   - Added proper type hinted properties and methods return types
   - Method addInfo() is not available in the current version of Monologger library. so replaced it with info() method.
   - Grouped if statements.
   - Fixed use of potentially undefined variables.
   - Used inline simple expressions instead of storing them in variables. Will improve memory usage.
   - Converted switch statement to match (latest in php 8)
   - Extracted common parts in 'if' statement.
   - Helper function array_only() is not available is current version of laravel. replaced with \Arr::only()
   - Removed unused variables and some other fixes.
   - Simplified the if statements where possible
   - Condition is always 'false' because parent condition is already 'true' at this point. so removed the elseif
   - Fixed - Optional parameter should be provided after required parameters.
   - Condition is unnecessary because it is already checked by isset

3. DTApi/Repository/BaseRepository.php
   - Added proper type hinted properties and methods return types
   - PHP Docs added for some functions

### REFACTOR THE CODE
- Optimized line of codes
- Readable
- Dedicated Methods
- Naming
- Close to English


