package za.ac.cput.Repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import za.ac.cput.Domain.Author;

@Repository
public interface AuthorRepository extends JpaRepository<Author, Long> {
}
