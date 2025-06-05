package za.ac.cput.Repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import za.ac.cput.Domain.Book;

@Repository
public interface BookRepository extends JpaRepository<Book, Long> {

}
